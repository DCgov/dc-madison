/*global window, document*/
angular.module( 'madisonApp.services' )
  .service( 'VendorService', function( $http, $window ) {
    var vendorService = {};

    vendorService.installUservoice = function() {
      var uservoiceHash = vendorService.settings.uservoice;

      if ( !uservoiceHash ) {
        console.error( 'Unable to load UserVoice.  Settings: %o', vendorService.settings.uservoice );
        return;
      }

      //Install Uservoice ( copied from uservoice embed script )
      //var UserVoice = window.UserVoice || [];
      window.UserVoice = [];

      var uv = document.createElement( 'script' );
      uv.type = 'text/javascript';
      uv.async = true;
      uv.src = '//widget.uservoice.com/' + uservoiceHash + '.js';
      var s = document.getElementsByTagName( 'script' )[0];
      s.parentNode.insertBefore( uv, s );

      // Set colors
      window.UserVoice.push( [ 'set', {
        accent_color: '#448dd6',
        trigger_color: 'white',
        trigger_background_color: 'rgba(46, 49, 51, 0.6)'
      } ] );

      // Identify the user and pass traits
      // To enable, replace sample data with actual user traits and uncomment the line
      window.UserVoice.push( [ 'identify', {

        //email:      'john.doe@example.com', // User’s email address
        //name:       'John Doe', // User’s real name
        //created_at: 1364406966, // Unix timestamp for the date the user signed up
        //id:         123, // Optional: Unique id of the user (if set, this should not change)
        //type:       'Owner', // Optional: segment your users by type
        //account: {
        //  id:           123, // Optional: associate multiple users with a single account
        //  name:         'Acme, Co.', // Account name
        //  created_at:   1364406966, // Unix timestamp for the date the account was created
        //  monthly_rate: 9.99, // Decimal; monthly rate of the account
        //  ltv:          1495.00, // Decimal; lifetime value of the account
        //  plan:         'Enhanced' // Plan name for the account
        //}
      } ] );

      // Add default trigger to the bottom-right corner of the window:
      window.UserVoice.push( [ 'addTrigger', {
        mode: 'contact',
        trigger_position: 'bottom-right'
      } ] );

      // Or, use your own custom trigger:
      //UserVoice.push(['addTrigger', '#id', { mode: 'contact' }]);

      // Autoprompt for Satisfaction and SmartVote (only displayed under certain conditions)
      window.UserVoice.push( [ 'autoprompt', {} ] );
    };

    vendorService.installGA = function() {
      var gaAccount = vendorService.settings.ga;

      if ( !gaAccount ) {
        console.error( 'Unable to load UserVoice.  Settings: %o', vendorService.settings.uservoice );
        return;
      }

      //Picking apart the GA embed script
      /*
        i = window;
        s = document;
        o = 'script';
        g = '//www.google-analytics.com/analytics.js';
        r = 'ga';
      */

      //i['GoogleAnalyticsObject'] = r;
      window.GoogleAnalyticsObject = 'ga';

      //i[r] = i[r] || function() {( i[r].q = i[r].q || [] ).push( arguments )}
      window.ga = window.ga || function() {
        ( window.ga.q || [] ).push( arguments );
      };

      //i[r].l = 1 * new Date();
      window.ga.l = 1 * new Date();

      //a = s.createElement( o )
      var script = document.createElement( 'script' );

      //m = s.getElementsByTagName( o )[0];
      var scripts = document.getElementsByTagName( 'script' )[0];

      //a.async = 1;
      script.async = 1;

      //a.src = g;
      script.src = '//www.google-analytics.com/analytics.js';

      //m.parentNode.insertBefore( a, m )
      scripts.parentNode.insertBefore( script, scripts );

      ga( 'create', 'UA-42400665-9', 'mymadison.io' );
      ga( 'send', 'pageview' );
    };

    vendorService.installVendors = function() {
      $http.get( '/api/settings/vendors' )
        .success( function( data ) {
          vendorService.settings = {
            "uservoice": data.uservoice,
            "ga": data.ga
          };

          //Install the vendor libraries
          vendorService.installUservoice();
          vendorService.installGA();
        } ).error( function() {
          console.error( 'Unable to load settings for vendor libraries!' );
        } );
    };

    return vendorService;

  } );
