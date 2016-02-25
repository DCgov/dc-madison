<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Collection;
use Event;
use URL;

class Doc extends Model
{
    use SoftDeletes;

    public static $timestamp = true;

    protected $index;

    protected $dates = ['deleted_at'];
    protected $appends = ['featured', 'url'];

    protected $fillable = ['discussion_state', 'publish_state'];

    const TYPE = 'doc';

    const SPONSOR_TYPE_INDIVIDUAL = "individual";
    const SPONSOR_TYPE_GROUP = "group";

    const PUBLISH_STATE_PUBLISHED = 'published';
    const PUBLISH_STATE_UNPUBLISHED = 'unpublished';
    const PUBLISH_STATE_PRIVATE = 'private';
    const PUBLISH_STATE_DELETED_ADMIN = 'deleted-admin';
    const PUBLISH_STATE_DELETED_USER = 'deleted-user';

    const DISCUSSION_STATE_OPEN = 'open';
    const DISCUSSION_STATE_CLOSED = 'closed';
    const DISCUSSION_STATE_HIDDEN = 'hidden';

    public function __construct()
    {
        parent::__construct();

        $this->index = \Config::get('elasticsearch.annotationIndex');
    }

    public function getEmbedCode()
    {
        $dom = new \DOMDocument();

        $docSrc = URL::to('docs/embed', $this->slug);

        $insertElement = $dom->createElement('div');

        $containerElement = $dom->createElement('iframe');
        $containerElement->setAttribute('id', '__ogFrame');
        $containerElement->setAttribute('width', 300);
        $containerElement->setAttribute('height', 500);
        $containerElement->setAttribute('src', $docSrc);
        $containerElement->setAttribute('frameBorder', 0);

        $insertElement->appendChild($containerElement);

        return $dom->saveHtml($insertElement);
    }

    public function introtext()
    {
        return $this->hasMany('App\Models\DocMeta')->where('meta_key', '=', 'intro-text');
    }

    public function dates()
    {
        return $this->hasMany('App\Models\Date');
    }

    public function getFeaturedAttribute()
    {
        $featuredSetting = Setting::where('meta_key', '=', 'featured-doc')->first();

        if ($featuredSetting) {
            $docIds = explode(',', $featuredSetting->meta_value);
            return in_array($this->id, $docIds);
        }

        return false;
    }

    public function canUserEdit($user)
    {
        $sponsor = $this->sponsor()->first();

        if ($user->hasRole('Admin')) {
            return true;
        }

        switch (true) {
            case $sponsor instanceof User:
                return $sponsor->hasRole('Independent Sponsor');
                break;
            case $sponsor instanceof Group:
                return $sponsor->userHasRole($user, Group::ROLE_EDITOR) || $sponsor->userHasRole($user, Group::ROLE_OWNER);
                break;
            default:
                throw new \Exception("Unknown Sponsor Type");
        }

        return false;
    }

    public function canUserView($user)
    {
        $sponsor = $this->sponsor->first();

        if (in_array(
            $this->publish_state,
            [Doc::PUBLISH_STATE_PUBLISHED, Doc::PUBLISH_STATE_PRIVATE]
        )) {
            return true;
        }

        if ($user) {
            if ($user->hasRole('Admin')) {
                return true;
            }

            if (
                $this->publish_state == Doc::PUBLISH_STATE_UNPUBLISHED
                && $this->canUserEdit($user)
            ) {
                return true;
            }
        }

        return false;
    }

    public function sponsor()
    {
        $sponsor = $this->belongsToMany('App\Models\Group')->first();

        if (!$sponsor) {
            return $this->belongsToMany('App\Models\User');
        }

        return $this->belongsToMany('App\Models\Group');
    }

    public function userSponsors()
    {
        return $this->belongsToMany('App\Models\User');
    }

    public function groupSponsors()
    {
        return $this->belongsToMany('App\Models\Group');
    }

    public function statuses()
    {
        return $this->belongsToMany('App\Models\Status');
    }

    public function categories()
    {
        return $this->belongsToMany('App\Models\Category');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }

    public function getPages()
    {
        return $this->content()->count();
    }

    public function getPagesAttribute()
    {
        return $this->getPages();
    }

    public function getCommentCount()
    {
        return $this->comments()->count();
    }

    public function getCommentCountAttribute()
    {
        return $this->getCommentCount();
    }

    public function getAnnotationCount()
    {
        return $this->annotations()->count();
    }

    public function getAnnotationCountAttribute()
    {
        return $this->getAnnotationCount();
    }

    public function getAnnotationCommentCount()
    {
        return count($this->getAnnotationComments());
    }

    public function getAnnotationCommentCountAttribute()
    {
        return $this->getAnnotationCommentCount();
    }

    public function getUserCount()
    {

        //Return user objects with only user_id property
        $annotationUsers = \DB::table('annotations')
            ->where('doc_id', '=', $this->id)
            ->get(['user_id']);

        //Returns user objects with only user_id property
        $commentUsers = \DB::table('comments')
            ->where('doc_id', '=', $this->id)
            ->get(['user_id']);

        //Returns user objects with only user_id property
        $annotationCommentUsers = \DB::table('annotation_comments')
            ->join('annotations', function ($join) {
                $join->on('annotation_comments.annotation_id', '=', 'annotations.id')
                    ->where('annotations.doc_id', '=', $this->id);
            })
            ->get(['annotation_comments.user_id']);

        //Merge object arrays
        $users = array_merge($annotationUsers, $commentUsers, $annotationCommentUsers);

        //Grab only the user_id attributes
        $userArray = array_map(function ($user) {
            return $user->user_id;
        }, $users);

        //Return the count of the array with uniques filtered
        return count(array_unique($userArray));
    }

    public function getUserCountAttribute()
    {
        return $this->getUserCount();
    }

    public function getSupportAttribute()
    {
        return DocMeta::where('meta_key', '=', 'support')->where('meta_value', '=', '1')->where('doc_id', '=', $this->id)->count();
    }

    public function getOpposeAttribute()
    {
        return DocMeta::where('meta_key', '=', 'support')->where('meta_value', '=', '')->where('doc_id', '=', $this->id)->count();
    }

    /*
     * Add the "count" fields before serializing.
     */
    public function enableCounts()
    {
        $this->appends[] = 'pages';
        $this->appends[] = 'comment_count';
        $this->appends[] = 'annotation_count';
        $this->appends[] = 'annotation_comment_count';
        $this->appends[] = 'user_count';
        $this->appends[] = 'support';
        $this->appends[] = 'oppose';
    }

    public function getSponsors()
    {
        $sponsors = [];

        foreach($this->userSponsors()->get() as $user) {
            $sponsors[] = [
                'display_name' => $user->display_name
            ];
        }

        foreach($this->groupSponsors()->get() as $group) {
            $sponsors[] = [
                'display_name' => $group->display_name
            ];
        }

        return $sponsors;
    }

    public function getSponsorsAttribute()
    {
        return $this->getSponsors();
    }

    public function enableSponsors()
    {
        $this->appends[] = 'sponsors';
    }

    public function annotations()
    {
        return $this->hasMany('App\Models\Annotation');
    }

    public function getAnnotationComments()
    {
        $annotationComments = \DB::table('annotation_comments')
            ->join('annotations', function ($join) {
                $join->on('annotation_comments.annotation_id', '=', 'annotations.id')
                    ->where('annotations.doc_id', '=', $this->id);
            })->get();

        return $annotationComments;
    }

    public function getLink()
    {
        return URL::to('docs/'.$this->slug);
    }

    /*
     * Wrapper for automatically getting the url value.
     */
    public function getUrlAttribute()
    {
        return $this->getLink();
    }

    public function content()
    {
        return $this->hasMany('App\Models\DocContent');
    }

    public function doc_meta()
    {
        return $this->hasMany('App\Models\DocMeta');
    }

    public static function createEmptyDocument(array $params)
    {
        $defaults = array(
            'content' => "New Document Content",
            'sponsor' => null,
            'sponsorType' => null,
            'publish_state' => 'unpublished'
        );

        $params = array_replace_recursive($defaults, $params);

        if (is_null($params['sponsor'])) {
            throw new \Exception("Sponsor Param Required");
        }

        $document = new Doc();

        \DB::transaction(function () use ($document, $params) {
            $document->title = $params['title'];
            $document->publish_state = $params['publish_state'];
            $document->save();

            switch ($params['sponsorType']) {
                case static::SPONSOR_TYPE_INDIVIDUAL:
                    $document->userSponsors()->sync(array($params['sponsor']));
                    break;
                case static::SPONSOR_TYPE_GROUP:
                    $document->groupSponsors()->sync(array($params['sponsor']));
                    break;
                default:
                    throw new \Exception("Invalid Sponsor Type");
            }

            $template = new DocContent();
            $template->doc_id = $document->id;
            $template->content = $params['content'];
            $template->save();

            $document->init_section = $template->id;
            $document->save();
        });

        Event::fire(MadisonEvent::NEW_DOCUMENT, $document);

        return $document;
    }

    public static function prepareCountsAndDates($docs = array())
    {
        $return_docs = array();

        if ($docs) {
            foreach ($docs as $doc) {
                $doc->enableCounts();
                $doc->enableSponsors();

                $return_doc = $doc->toArray();

                $return_doc['updated_at'] = date('c', strtotime($return_doc['updated_at']));
                $return_doc['created_at'] = date('c', strtotime($return_doc['created_at']));
                $return_doc['deleted_at'] = date('c', strtotime($return_doc['deleted_at']));

                $return_docs[] = $return_doc;
            }
        }

        return $return_docs;
    }

    public function save(array $options = array())
    {
        if (empty($this->slug)) {
            $this->slug = $this->getSlug();
        }

        return parent::save($options);
    }

    public function getSlug()
    {
        if (empty($this->title)) {
            throw new Exception("Can't get a slug - empty title");
        }

        return str_replace(
                    array(' ', '.', ',', '#'),
                    array('-', '', '', ''),
                    strtolower($this->title));
    }

    /*
     * Simple wrapper for our most commonly used joins.
     */
    public static function getEager()
    {
        return Doc::with('categories')
            ->with('userSponsors')
            ->with('groupSponsors')
            ->with('statuses')
            ->with('dates');
    }

    /*
     * Active documents are much harder to query.  We do this with its own
     * custom query.
     */
    public static function getActive($num, $offset)
    {
        // Defaults to limit 10 because of the expense here.
        if (!$num) {
            $num = 10;
        }

        if (!$offset) {
            $offset = 0;
        }

        $docIds = \DB::select(
            \DB::raw(
                "SELECT doc_id, SUM(num) AS total FROM (
                    SELECT doc_id, COUNT(*) AS num
                        FROM annotations
                        GROUP BY doc_id
                    UNION ALL
                    SELECT doc_id, COUNT(*) AS num
                        FROM comments
                        GROUP BY doc_id
                    UNION ALL
                    SELECT annotations.doc_id, COUNT(*) AS num
                        FROM annotation_comments
                        INNER JOIN annotations
                        ON annotation_comments.annotation_id = annotations.id
                        GROUP BY doc_id

                ) total_count
                LEFT JOIN docs on doc_id = docs.id
                WHERE publish_state = 'published'
                AND discussion_state = 'open'
                AND docs.is_template != 1
                GROUP BY doc_id
                ORDER BY total DESC
                LIMIT :offset, :limit"
            ),
            array(
                ':offset' => $offset,
                ':limit' => $num
            )
        );

        $docArray = [];

        //Create array of [id => total] for each document
        foreach ($docIds as $docId) {
            $docArray[$docId->doc_id] = $docId->total;
        }
        $docs = false;

        if (count($docArray) > 0) {
            //Grab out most active documents
            $docs = Doc::getEager()->whereIn('id', array_keys($docArray))->get();

            //Set the sort value to the total count
            foreach ($docs as $doc) {
                $doc->participationTotal = $docArray[$doc->id];
            }

            //Sort by the sort value descending
            $docs = $docs->sortByDesc('participationTotal');
        }

        return $docs;
    }

    public static function allOwnedBy($userId)
    {
        $rawDocs = \DB::select(
            \DB::raw(
                "SELECT docs.* FROM
					(SELECT doc_id
					   FROM doc_group, group_members
					  WHERE doc_group.group_id = group_members.group_id
					    AND group_members.user_id = ?
					UNION ALL
					 SELECT doc_id
					   FROM doc_user
					  WHERE doc_user.user_id = ?
				    ) DocUnion, docs
				  WHERE docs.id = DocUnion.doc_id
			   GROUP BY docs.id"
            ),
            array($userId, $userId)
        );

        $results = new Collection();

        foreach ($rawDocs as $row) {
            $obj = new static();

            foreach ($row as $key => $val) {
                $obj->$key = $val;
            }

            $results->add($obj);
        }

        return $results;
    }

    public static function getAllValidSponsors()
    {
        $userMeta = UserMeta::where('meta_key', '=', UserMeta::TYPE_INDEPENDENT_SPONSOR)
                            ->where('meta_value', '=', 1)
                            ->get();

        $groups = Group::where('status', '=', Group::STATUS_ACTIVE)
                        ->get();

        $results = new Collection();

        $userIds = array();

        foreach ($userMeta as $m) {
            $userIds[] = $m->user_id;
        }

        if (!empty($userIds)) {
            $users = User::whereIn('id', $userIds)->get();

            foreach ($users as $user) {
                $row = array(
                        'display_name' => "{$user->fname} {$user->lname}",
                        'sponsor_type' => 'individual',
                        'id' => $user->id,
                );

                $results->add($row);
            }
        }

        foreach ($groups as $group) {
            $row = array(
                    'display_name' => $group->display_name,
                    'sponsor_type' => 'group',
                    'id' => $group->id,
            );

            $results->add($row);
        }

        return $results;
    }

    public function get_file_path($format = 'markdown')
    {
        switch ($format) {
            case 'html' :
                $path = 'html';
                $ext = '.html';
                break;

            case 'markdown':
            default:
                $path = 'md';
                $ext = '.md';
        }

        $filename = $this->slug.$ext;
        $path = implode(DIRECTORY_SEPARATOR, array(storage_path(), 'docs', $path, $filename));

        return $path;
    }

    public function indexContent($doc_content)
    {
        $es = self::esConnect();

        \File::put($this->get_file_path('markdown'), $doc_content->content);

        \File::put($this->get_file_path('html'),
            Markdown::render($doc_content->content)
        );

        $body = array(
            'id' => $this->id,
            'content' => $doc_content->content,
        );

        $params = array(
            'index'    => $this->index,
            'type'    => self::TYPE,
            'id'    => $this->id,
            'body'    => $body,
        );

        $results = $es->index($params);
    }

    public function get_content($format = null)
    {
        $path = $this->get_file_path($format);

        try {
            return \File::get($path);
        } catch (Illuminate\Filesystem\FileNotFoundException $e) {
            $content = DocContent::where('doc_id', '=', $this->attributes['id'])->where('parent_id')->first()->content;

            if ($format == 'html') {
                $content = Markdown::render($content);
            }

            return $content;
        }
    }

    public static function search($query)
    {
        $es = self::esConnect();

        $params['index'] = \Config::get('elasticsearch.annotationIndex');
        $params['type'] = self::TYPE;
        $params['body']['query']['filtered']['query']['query_string']['query'] = $query;

        return $es->search($params);
    }

    public static function esConnect()
    {
        $esParams['hosts'] = \Config::get('elasticsearch.hosts');
        $es = new \Elasticsearch\Client($esParams);

        return $es;
    }

    public static function findDocBySlug($slug = null)
    {
        //Retrieve requested document
        $doc = static::where('slug', $slug)
                     ->with('statuses')
                     ->with('userSponsors')
                     ->with('groupSponsors')
                     ->with('categories')
                     ->with('dates')
                     ->first();

        if (!isset($doc)) {
            return;
        }

        return $doc;
    }

    public static function validPublishStates()
    {
         return [
            'all',
            self::PUBLISH_STATE_PUBLISHED,
            self::PUBLISH_STATE_UNPUBLISHED,
            self::PUBLISH_STATE_PRIVATE,
            self::PUBLISH_STATE_DELETED_ADMIN,
            self::PUBLISH_STATE_DELETED_USER
        ];
    }

    public static function validPublishStatesRoutePattern()
    {
        $valid_states = self::validPublishStates();

        foreach ($valid_states as $idx => $state) {
            $valid_states[$idx] = str_replace('-', '\-', $state);
        }

        return '(' . implode('|', $valid_states) . ')';
    }

    public static function validDiscussionStates()
    {
        return [
            self::DISCUSSION_STATE_OPEN,
            self::DISCUSSION_STATE_CLOSED,
            self::DISCUSSION_STATE_HIDDEN
        ];
    }

    /**
     * Scopes!
     * -----------------------------------------------
     */

    /**
     * Scope to return documents that the user has edit access to
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBelongsToUser($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            // doc has independent sponsor
            $q->whereHas('userSponsors', function($q) use ($userId) {
                // independent sponsor is current user
                $q->where('id', '=', $userId);
            });
            // doc has group sponsor
            $q->orWhereHas('groupSponsors', function($q) use ($userId) {
                // user belongs to group as EDITOR or OWNER
                $q->whereHas('members', function($q) use ($userId) {
                    $q->where('user_id', '=', $userId);
                    $q->whereIn('role', [Group::ROLE_EDITOR, Group::ROLE_OWNER]);
                });
            });
        });
    }

    public function getImagePath($image = '')
    {
        return 'doc-' . $this->id . '/' . $image;
    }

    public function getImageUrl($image = '')
    {
        return '/api/docs/' . $this->id . '/images/' . $image;
    }

    public function getImagePathFromUrl($image)
    {
        return str_replace('/api/docs/' . $this->id . '/images/',
            'doc-' . $this->id . '/', $image);
    }
}
