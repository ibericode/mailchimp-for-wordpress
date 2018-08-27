<?php

/**
 *
 * Based on class phpListRESTApiClient.
 *
 */

 /**
 *
 * example PHP client class to access the phpList Rest API.
 * License: MIT, https://opensource.org/licenses/MIT
 *
 * To use this class, you need the restapi plugin for phpList, https://resources.phplist.com/plugin/restapi
 * Set the parameters below to match your system:
 *
 * - url                    : URL of your phpList installation
 * - loginName              : admin login
 * - password               : matching password
 * - remoteProcessingSecret : (optional) the secret as defined in your phpList settings
 *
 * v 1.01 Nov 26, 2015 added optional secret on instantiation
 * v 1 * Michiel Dethmers, phpList Ltd, November 18, 2015
 *    Initial implementation of basic API calls
 */
class PL4WP_API_Client
{
    /**
     * URL of the API to connect to including the path
     * generally something like.
     *
     * https://website.com/lists/admin/?pi=restapi&page=call
     */
    private $url;
    /**
     * login name for the phpList installation.
     */
    private $loginName;

    /**
     * password to login.
     */
    private $password;

    /**
     * the path where we can write our cookiejar.
     */
    public $tmpPath = '/tmp';

    /**
     * optionally the remote processing secret of the phpList installation
     * this will increase the security of the API calls.
     */
    private $remoteProcessingSecret;
	
	/**
	 * @var bool Successful login to PhpList API?
	 */
	protected $logged_in = false;
	
	/**
     * construct, provide the Credentials for the API location.
     *
     * @param string $url URL of the API
     * @param string $loginName name to login with
     * @param string $password password for the account
     * @return null
     */
    public function __construct($url, $loginName, $password, $secret = '')
    {
        $this->url = $url . '/admin/?page=call&pi=restapi';
        $this->loginName = $loginName;
        $this->password = $password;
        $this->remoteProcessingSecret = $secret;
    }
	
	protected function do_login() {
		
		if (!$this->logged_in) {
			$this->logged_in = $this->login();
		}
		if (!$this->logged_in) {
			throw new PL4WP_API_Exception('Invalid credentials');
		}
		
		return $this->logged_in;
	}
	
	/**
     * Make a call to the API using cURL.
     *
     * @param string $command The command to run
     * @param array $post_params Array for parameters for the API call
     * @param bool $decode json_decode the result (defaults to true)
     *
     * @return string result of the CURL execution
     */
    private function callApi($command, $post_params, $decode = true)
    {
        $post_params['cmd'] = $command;

        // optionally add the secret to a call, if provided
        if (!empty($this->remoteProcessingSecret)) {
            $post_params['secret'] = $this->remoteProcessingSecret;
        }
        $post_params = http_build_query($post_params);
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL,            $this->url);
        curl_setopt($c, CURLOPT_HEADER,         0);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_POST,           1);
        curl_setopt($c, CURLOPT_POSTFIELDS,     $post_params);
        curl_setopt($c, CURLOPT_COOKIEFILE,     $this->tmpPath.'/phpList_RESTAPI_cookiejar.txt');
        curl_setopt($c, CURLOPT_COOKIEJAR,      $this->tmpPath.'/phpList_RESTAPI_cookiejar.txt');
        curl_setopt($c, CURLOPT_HTTPHEADER,     array('Connection: Keep-Alive', 'Keep-Alive: 60'));

        // Execute the call
        $result = curl_exec($c);
        // Check if decoding of result is required
        if ($decode === true) {
            $result = json_decode($result);
        }

        return $result;
    }

    /**
     * Use a real login to test login api call.
     *
     * @param none
     *
     * @return bool true if user exists and login successful
     */
    public function login()
    {
        // Set the username and pwd to login with
        $post_params = array(
            'login' => $this->loginName,
            'password' => $this->password,
        );

        // Execute the login with the credentials as params
        $result = $this->callApi('login', $post_params);
        return $result->status == 'success';
    }

    /**
     * Create a list.
     *
     * @param string $listName        Name of the list
     * @param string $listDescription Description of the list
     *
     * @return int ListId of the list created
     */
    public function listAdd($listName, $listDescription)
    {
    	$this->do_login();
    	
        // Create minimal params for api call
        $post_params = array(
            'name' => $listName,
            'description' => $listDescription,
            'listorder' => '0',
            'active' => '1',
        );

        // Execute the api call
        $result = $this->callAPI('listAdd', $post_params);

        // get the ID of the list we just created
        $listId = $result->data->id;

        return $listId;
    }

    /**
     * Get all lists.
     *
     * @return array All lists
     */
    public function listsGet()
    {
	    $this->do_login();

        // Create minimal params for api call
        $post_params = array(
        );

        // Execute the api call
        $result = $this->callAPI('listsGet', $post_params);

        // Return all list as array
        return $result->data;
    }

    /**
     * Find a subscriber by email address.
     *
     * @param string $emailAddress Email address to search
     *
     * @return int $subscriberID if found false if not found
     */
    public function subscriberFindByEmail($emailAddress)
    {
	    $this->do_login();

        $params = array(
            'email' => $emailAddress,
        );
        $result = $this->callAPI('subscriberGetByEmail', $params);

        if (!empty($result->data->id)) {
            return $result->data->id;
        } else {
            return false;
        }
    }

    public function subscriberAdd($emailAddress)
    {
	    $this->do_login();

        // Set the user details as parameters
        $post_params = array(
            'email' => $emailAddress,
            'foreignkey' => '',
            'htmlemail' => 1,
            'subscribepage' => 0,
            'confirmed' => 1
        );

        // Execute the api call
        $result = $this->callAPI('subscriberAdd', $post_params);

        if (!empty($result->data->id)) {
            $subscriberId = $result->data->id;

            return $subscriberId;
        } else {
            return false;
        }
    }

    /**
     * Add a subscriber.
     *
     * This is the main method to use to add a subscriber. It will add the subscriber as
     * a non-confirmed subscriber in phpList and it will send the Request-for-confirmation
     * email as set up in phpList.
     *
     * The lists parameter will set the lists the subscriber will be added to. This has
     * to be comma-separated list-IDs, eg "1,2,3,4".
     *
     * @param string $emailAddress email address of the subscriber to add
     * @param string $lists        comma-separated list of IDs of the lists to add the subscriber to
     *
     * @return int $subscriberId if added, or false if failed
     */
    public function subscribe($emailAddress, $lists)
    {
	    $this->do_login();

        // Set the user details as parameters
        $post_params = array(
            'email' => $emailAddress,
            'foreignkey' => '',
            'htmlemail' => 1,
            'subscribepage' => 0,
            'lists' => $lists,
        );

        // Execute the api call
        $result = $this->callAPI('subscribe', $post_params);

        if (!empty($result->data->id)) {
            $subscriberId = $result->data->id;

            return $subscriberId;
        } else {
            return false;
        }
    }

    /**
     * Fetch subscriber by ID.
     *
     * @param int $subscriberID ID of the subscriber
     *
     * @return the subscriber
     */
    public function subscriberGet($subscriberId)
    {
	    $this->do_login();

        $post_params = array(
            'id' => $subscriberId,
        );

        // Execute the api call
        $result = $this->callAPI('subscriberGet', $post_params);
        if (!empty($result->data->id)) {
            return $result->data;
        } else {
            return false;
        }
    }

    /**
     * Get a subscriber by Foreign Key.
     *
     * Note the difference with subscriberFindByEmail which only returns the SubscriberID
     * Both API calls return the subscriber
     *
     * @param string $foreignKey Foreign Key to search
     *
     * @return subscriber object if found false if not found
     */
    public function subscriberGetByForeignkey($foreignKey)
    {
	    $this->do_login();

        $post_params = array(
            'foreignkey' => $foreignKey,
        );

        $result = $this->callAPI('subscriberGetByForeignkey', $post_params);
        if (!empty($result->data->id)) {
            return $result->data;
        } else {
            return false;
        }
    }
	
	/**
	 * Remove subscriber
	 *
	 * @param int $subscriberID ID of the subscriber
	 *
	 * @return bool
	 */
	public function subscriberDelete($subscriberId)
	{
		$this->do_login();
		
		$post_params = array(
			'id' => $subscriberId,
		);
		
		// Execute the api call
		$result = $this->callAPI('subscriberDelete', $post_params);
		return $result->status == 'success';
	}

	/**
      * Get the total number of subscribers.
      *
      * @param none
      *
      * @return int total number of subscribers in the system
      */
     public function subscriberCount()
     {
	     $this->do_login();

	     $post_params = array(
         );

         $result = $this->callAPI('subscribersCount', $post_params);

         return $result->data->total;
     }

    /**
     * Add a subscriber to an existing list.
     *
     * @param int $listId       ID of the list
     * @param int $subscriberId ID of the subscriber
     *
     * @return the lists this subscriber is member of
     */
    public function listSubscriberAdd($listId, $subscriberId)
    {
	    $this->do_login();

        // Set list and subscriber vars
        $post_params = array(
            'list_id' => $listId,
            'subscriber_id' => $subscriberId,
        );

        $result = $this->callAPI('listSubscriberAdd', $post_params);

        return $result->data;
    }

    /**
     * Get the lists a subscriber is member of.
     *
     * @param int $subscriberId ID of the subscriber
     *
     * @return the lists this subcriber is member of
     */
    public function listsSubscriber($subscriberId)
    {
	    $this->do_login();

        $post_params = array(
            'subscriber_id' => $subscriberId,
        );

        $result = $this->callAPI('listsSubscriber', $post_params);

        return $result->data;
    }

    /**
     * Remove a Subscriber from a list.
     *
     * @param int $listId       ID of the list to remove
     * @param int $subscriberId ID of the subscriber
     *
     * @return the lists this subcriber is member of
     */
    public function listSubscriberDelete($listId, $subscriberId)
    {
	    $this->do_login();

        // Set list and subscriber vars
        $post_params = array(
            'list_id' => $listId,
            'subscriber_id' => $subscriberId,
        );

        $result = $this->callAPI('listSubscriberDelete', $post_params);

        return $result->data;
    }
}
