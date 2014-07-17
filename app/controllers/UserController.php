<?php
/**
 * 	Controller for user actions
 */
class UserController extends BaseController{
	
	public function __construct(){
		parent::__construct();
	}
	
	public function getIndex(User $user){
		//Set data array
		$data = array(
			'user'			=> $user,
			'page_id'		=> 'user_profile',
			'page_title'	=> $user->fname . ' ' . substr($user->lname, 0, 1) . "'s Profile"	
		);
	
		//Render view and return
		return View::make('user.index', $data);
	}
	
	public function getEdit($id=null){
		if(!Auth::check()){
			return Redirect::to('user/login')->with('error', 'Please log in to edit user profile');
		}else if(Auth::user()->id != $id){
			return Redirect::back()->with('error', 'You do not have access to that profile.');
		}else if($id == null){
			return Response::error('404');
		}

		//Set data array
		$data = array(
			'page_id'		=> 'edit_profile',
			'page_title'	=> 'Edit Your Profile'
		);
		
		return View::make('user.edit.index', $data);
	}
	
	public function putEdit($id=null){
		if(!Auth::check()){
			return Redirect::to('user/login')->with('error', 'Please log in to edit user profile');
		}else if(Auth::user()->id != $id){
			return Redirect::back()->with('error', 'You do not have access to that profile.');
		}else if($id == null){
			return Response::error('404');
		}
		
		$email = Input::get('email');
		$fname = Input::get('fname');
		$lname = Input::get('lname');
		$url = Input::get('url');
		$phone = Input::get('phone');
		$verify = Input::get('verify');

		$user_details = Input::all();
		
		if(Auth::user()->email != $email) {
			$rules = array(
				'fname'		=> 'required',
				'lname'		=> 'required',
				'email'		=> 'required|unique:users'
			);
		} else {
			$rules = array(
				'fname'		=> 'required',
				'lname'		=> 'required',
				'phone'		=> 'required'
			);
		}
		
		$validation = Validator::make($user_details, $rules);
		
		if($validation->fails()){
			return Redirect::to('user/edit/' . $id)->withInput()->withErrors($validation);
		}
		
		$user = User::find($id);
		$user->email = $email;
		$user->fname = $fname;
		$user->lname = $lname;
		$user->url = $url;
		$user->phone = $phone;
		$user->save();

		if(isset($verify)){
			$meta = new UserMeta();
			$meta->meta_key = 'verify';
			$meta->meta_value = 'pending';
			$meta->user_id = $id;
			$meta->save();

			return Redirect::back()->with('success_message', 'Your profile has been updated')->with('message', 'Your verified status has been requested.');
		}
		
		return Redirect::back()->with('success_message', 'Your profile has been updated.');
	}
	
	public function putIndex($id = null){
		return Response::error('404');
	}
	
	public function postIndex($id = null){
		return Response::error('404');
	}

	public function getLogin(){
		$previous_page = Input::old('previous_page');
		
		if(!isset($previous_page)){
			$previous_page = URL::previous();
		}

		$data = array(
			'page_id'		=> 'login',
			'page_title'	=> 'Log In',
			'previous_page'	=> $previous_page
		);

		return View::make('login.index', $data);
	}

	public function postLogin(){
		//Retrieve POST values
		$email = Input::get('email');
		$password = Input::get('password');
		$previous_page = Input::get('previous_page');
		$remember = Input::get('remember');
		$user_details = Input::all();

		//Rules for login form submission
		$rules = array('email' => 'required', 'password' => 'required');
		$validation = Validator::make($user_details, $rules);
		
		//Validate input against rules
		if($validation->fails()){
			return Redirect::to('user/login')->withInput()->withErrors($validation);
		}
		
		//Check that the user account exists
		$user = User::where('email', $email)->first();
		
		if(!isset($user)){
			return Redirect::to('user/login')->with('error', 'That email does not exist.');
		}
		
		//If the user's token field isn't blank, he/she hasn't confirmed their account via email
		if($user->token != ''){
			return Redirect::to('user/login')->with('error', 'Please click the link sent to your email to verify your account.');
		}
		
		//Attempt to log user in
		$credentials = array('email' => $email, 'password' => $password);
		
		if(Auth::attempt($credentials, ($remember == 'true') ? true : false)){
			Auth::user()->last_login = new DateTime;
			Auth::user()->save();
			if(isset($previous_page)){
				return Redirect::to($previous_page)->with('message', 'You have been successfully logged in.');	
			}else{
				return Redirect::to('/docs/')->with('message', 'You have been successfully logged in.');
			}
		}
		else{
			return Redirect::to('user/login')->with('error', 'Incorrect login credentials')->withInput(array('previous_page' => $previous_page));
		}
	}

	/**
	 * 	GET Signup Page
	 */
	public function getSignup(){
		$data = array(
			'page_id'		=> 'signup',
			'page_title'	=> 'Sign Up for Madison'
		);
		
		return View::make('login.signup', $data);
	}

	/**
	 * 	POST to create user account
	 */
	public function postSignup(){
		//Retrieve POST values
		$email = Input::get('email');
		$password = Input::get('password');
		$fname = Input::get('fname');
		$lname = Input::get('lname');
		$user_details = Input::all();
		
		//Rules for signup form submission
		$rules = array('email'		=>	'required|unique:users',
						'password'	=>	'required',
						'fname'		=>	'required',
						'lname'		=>	'required'
						);
		$validation = Validator::make($user_details, $rules);
		if($validation->fails()){
			return Redirect::to('user/signup')->withInput()->withErrors($validation);
		}
		else{
			//Create user token for email verification
			$token = str_random();
			
			//Create new user
			$user = new User();
			$user->email = $email;
			$user->password = Hash::make($password);
			$user->fname = $fname;
			$user->lname = $lname;
			$user->token = $token;
			$user->save();
			
			//Send email to user for email account verification
			Mail::queue('email.signup', array('token'=>$token), function ($message) use ($email, $fname) {
    			$message->subject('Welcome to the Madison Community');
    			$message->from('sayhello@opengovfoundation.org', 'Madison');
    			$message->to($email); // Recipient address
			});
			
			return Redirect::to('user/login')->with('message', 'An email has been sent to your email address.  Please follow the instructions in the email to confirm your email address before logging in.');
		}
		
	}

	/**
	 * 	Verify users from link sent via email upon signup
	 */
	public function getVerify($token){
		echo $token;
		$user = User::where('token', $token)->first();
		
		if(isset($user)){
			$user->token = '';
			$user->save();
			
			Auth::login($user);
			
			return Redirect::to('/')->with('success_message', 'Your email has been verified and you have been logged in.  Welcome ' . $user->fname);
		}else{
			return Redirect::to('user/login')->with('error', 'The verification link is invalid.');
		}
		
	}
}
