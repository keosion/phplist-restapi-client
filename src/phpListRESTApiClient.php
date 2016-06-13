<?php

namespace UtagawaVtt;

use GuzzleHttp\Client as httpClient;

/**
 *
 * Class PhpListRESTAPIClient
 *
 *
 * This file is part of PhpListRESTAPIClient.
 *
 * PhpListRESTAPIClient is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 *  PhpListRESTAPIClient is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with PhpListRESTAPIClient.  If not, see <http://www.gnu.org/licenses/>
 *
 *
 * Original code base comes from examples in :
 * Test files in https://github.com/phpList/phplist-plugin-restapi (GPLv3 license)
 * Php client : https://github.com/michield/phplist-restapi-client (MIT License)
 *
 *
 * - url                    : URL of the phpList REST API
 * - login              : admin login
 * - password               : matching password
 * - remoteProcessingSecret : (optional) the secret as defined in your phpList settings
 *
 */

class PhpListRESTAPIClient
{
    /**
     * URL of the phpList REST API to connect
     * generally something like.
     *
     * https://website.com/lists/admin/?pi=restapi&page=call
     */
    private $url;
    /**
     * login name for the phpList installation.
     */
    private $login;

    /**
     * password to login.
     */
    private $password;

    /**
     * optionally the remote processing secret of the phpList installation
     * this will increase the security of the API calls.
     */
    private $remoteProcessingSecret;

    /**
     * http client
     */
    private $client;


    /**
     * construct, provide the Credentials for the API location.
     *
     * @param string $url URL of the API
     * @param string $login name to login with
     * @param string $password password for the account
     * @return null
     */
    public function __construct($url, $login, $password, $secret = '', $client = null)
    {
        $this->url = $url;
        $this->login = $login;
        $this->password = $password;
        $this->remoteProcessingSecret = $secret;

        // this approach allows use to test our code and reuse the same instance with specific configuration
        $this->client = $client;
        if ($this->client === null || !is_a($this->client, 'GuzzleHttp\Client')) {
            $this->client = new httpClient(
                array(
                    'base_uri' => '',
                    'timeout'  => 2.0,
                    'cookies' => true,
                )
            );
        }
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
        if (!empty($this->remoteProcessingSecret)) {
            $post_params['secret'] = $this->remoteProcessingSecret;     // optionally add the secret to a call, if provided
        }

        $response = $this->client->post($this->url, array('form_params' => $post_params));
        $result = $response->getBody();

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
        $post_params = array(
            'login' => $this->login,
            'password' => $this->password,
        );
        // Execute the login with the credentials as params
        $result = $this->callApi('login', $post_params);
        return $this->checkValidity($result);
    }

    /**
     * Get lists.
     *
     * @return array ListIds of the lists or false if a problem occured
     */
    public function listsGet()
    {
        $post_params = array();
        $result = $this->callAPI('listsGet', $post_params);
        return $this->checkValidity($result) && isset($result->data) ? $result->data : false;
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
        $post_params = array(
            'name' => $listName,
            'description' => $listDescription,
            'listorder' => '0',
            'active' => '1',
        );
        $result = $this->callAPI('listAdd', $post_params);
        // return the ID of the list we just created of false if something failed
        return $this->checkValidity($result) && isset($result->data) && isset($result->data->id) ? $result->data->id : false;
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
        $params = array(
            'email' => $emailAddress,
        );
        $result = $this->callAPI('subscriberGetByEmail', $params);
        if ($this->checkValidity($result) && isset($result->data) && isset($result->data->id) && !empty($result->data->id)) {
            return $result->data->id;
        } else {
            return false;
        }
    }

    /**
     * Subscribe.
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
        $post_params = array(
            'email' => $emailAddress,
            'foreignkey' => '',
            'htmlemail' => 1,
            'subscribepage' => 0,
            'lists' => $lists
        );
        $result = $this->callAPI('subscribe', $post_params);
        if ($this->checkValidity($result) && isset($result->data) && isset($result->data->id) && !empty($result->data->id)) {
            $subscriberId = $result->data->id;
            return $subscriberId;
        } else {
            return false;
        }
    }

    /**
     * Add a subscriber.
     *
     * This is the main method to use to add a subscriber. It will add the subscriber as a confirmed subscriber in phpList.
     *
     * @param string $emailAddress email address of the subscriber to add
     *
     * @return int $subscriberId if added, or false if failed
     */
    public function subscriberAdd($emailAddress)
    {
        $post_params = array(
            'email' => $emailAddress,
            'foreignkey' => '',
            'confirmed' => 1,
            'htmlemail' => 1,
            'disabled' => 0,
        );
        $result = $this->callAPI('subscriberAdd', $post_params);
        if ($this->checkValidity($result) && isset($result->data) && isset($result->data->id) && !empty($result->data->id)) {
            $subscriberId = $result->data->id;
            return $subscriberId;
        } else {
            return false;
        }
    }

    /**
     * Update a subscriber.
     *
     * This is the main method to use to Update a subscriber. It will update email address.
     *
     * @param int $subscriberId subscriber Id
     * @param string $newEmailAddress email address of the subscriber to add
     *
     * @return int $subscriberId if updated, or false if failed
     */
    public function subscriberUpdate($subscriberId, $newEmailAddress)
    {
        $post_params = array(
            'id' => $subscriberId,
            'email' => $newEmailAddress,
            'confirmed' => 1,
            'htmlemail' => 1,
        );
        $result = $this->callAPI('subscriberUpdate', $post_params);

        if ($this->checkValidity($result) && isset($result->data) && isset($result->data->id) && !empty($result->data->id)) {
            $subscriberId = $result->data->id;
            return $subscriberId;
        } else {
            return false;
        }
    }

    /**
     * Delete a subscriber.
     *
     * This is the main method to use to delete a subscriber.
     *
     * @param int $subscriberId subscriber Id
     *
     * @return int $subscriberId if deleted, or false if failed
     */
    public function subscriberDelete($subscriberId)
    {
        $post_params = array(
            'id' => $subscriberId,
        );
        $result = $this->callAPI('subscriberDelete', $post_params);
        return $this->checkValidity($result);
    }

    /**
     * Fetch subscriber by ID.
     *
     * @param int $subscriberId ID of the subscriber
     *
     * @return the subscriber
     */
    public function subscriberGet($subscriberId)
    {
        $post_params = array(
            'id' => $subscriberId,
        );
        $result = $this->callAPI('subscriberGet', $post_params);
        if ($this->checkValidity($result) && isset($result->data) && isset($result->data->id) && !empty($result->data->id)) {
            $fetchedSubscriberId = $result->data->id;
            return $fetchedSubscriberId == $subscriberId ? $result->data : false;
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
        $post_params = array(
            'foreignkey' => $foreignKey,
        );
        $result = $this->callAPI('subscriberGetByForeignkey', $post_params);
        if ($this->checkValidity($result) && isset($result->data) && isset($result->data->id) && !empty($result->data->id)) {
            return $result->data;
        } else {
            return false;
        }
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
        $post_params = array();
        $result = $this->callAPI('subscribersCount', $post_params);
        return $this->checkValidity($result) && isset($result->data) && isset($result->data->total) ? $result->data->total : false;
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
        $post_params = array(
            'list_id' => $listId,
            'subscriber_id' => $subscriberId,
        );
        $result = $this->callAPI('listSubscriberAdd', $post_params);
        return $this->checkValidity($result) && isset($result->data) ? $result->data : false;
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
        $post_params = array(
            'subscriber_id' => $subscriberId,
        );
        $result = $this->callAPI('listsSubscriber', $post_params);
        return $this->checkValidity($result) && isset($result->data) ? $result->data : false;
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
        $post_params = array(
            'list_id' => $listId,
            'subscriber_id' => $subscriberId,
        );
        $result = $this->callAPI('listSubscriberDelete', $post_params);
        return $this->checkValidity($result) && isset($result->data) ? $result->data : false;
    }

    /**
     * Check response data validity
     *
     * @return boolean true if valid, false if not
     */
    private function checkValidity($result)
    {
        return isset($result->status) && $result->status === 'success';
    }
}
