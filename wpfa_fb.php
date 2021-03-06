<?php
//include facebook php sdk
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Facebook Page object.
 */
class wpfa_FbPage{
    private $fb;
    private $token;

    function __construct($app_id, $app_secret, $token){
        $this->token = $token;
        $this->fb = new Facebook\Facebook([
            'app_id' => $app_id,
            'app_secret' => $app_secret,
            'default_graph_version' => 'v2.4'
        ]);
    }

    function call_graph_api($request){
        try {
            $response = $this->fb->get($request, $this->token);
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            error_log('Graph returned an error: ' . $e->getMessage() . 'with request: ' . $request);
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            error_log('Facebook SDK returned an error: ' . $e->getMessage() . 'with request: ' . $request);
        }
        return $response;
    }

    function get_posts($page_ID){
        $request = '/'.$page_ID.'/posts?limit=10&fields=id,created_time';
        $response = $this->call_graph_api($request);
        return $response->getGraphEdge();
    }

    function get_page_name($page_ID){
        $request = '/'.$page_ID.'?fields=name';
        $response = $this->call_graph_api($request);
        $object = $response->getGraphNode();
        return $object['name'];
    }

    function get_attachments($post_id){
        $request = '/'.$post_id.'/attachments';
        $response = $this->call_graph_api($request);
        return $response->getGraphEdge();
    }

    function get_fb_video_embed($video_id){
        $request = '/'.$video_id.'?fields=embed_html';
        $response = $this->call_graph_api($request);
        $video_object = $response->getGraphNode();
        return $video_object['embed_html'];
    }

    function get_post($post_id){
        $p['id'] = $post_id;
        $p['images'] = array();
        $embed_video = NULL;

        $request = '/'.$post_id.'?fields=full_picture,message,status_type,object_id';
        $response = $this->call_graph_api($request);
        $post = $response->getGraphNode();

        //handle different post types
        switch ($post['status_type']) {
            case 'added_photos':
                $a = $this->get_attachments($post_id);
                switch ($a[0]['type']) {
                    case 'album':
                        foreach ($a[0]['subattachments'] as $sub) {
                            array_push($p['images'], $sub['media']['image']['src']);
                        }
                        break;

                    case 'photo':
                        $p['images'][0] = $a[0]['media']['image']['src'];
                        break;
                    default:
                        $p['images'] = NULL;
                        break;
                }
                break;
            case 'added_video':
                $embed_video = $this->get_fb_video_embed($post['object_id']);
                //scrape image
                $p['images'][0] = $post['full_picture'];
                break;
            case 'shared_story':
                //attempt to scrape an image if it exists
                if ($post['full_picture']) {
                    $p['images'][0] = $post['full_picture'];
                }
                else {
                    $p['images'] = NULL;
                }
                break;
            default:
                return NULL;
                break;
        }

        //don't generate empty posts
        if ($post['message'] == '') {
            return NULL;
        }

        //create post excerpt
        $p['excerpt'] = $post['message'];

        //
        //insert embed tags around links
        $message = preg_replace("/http(s|):\/\/\S+/", '[embed]$0[/embed]', $post['message']);

        //embed video if one is attached to the post
        if ($embed_video){
            $message = $message . '<br><br>' . $embed_video;
        }

        //get the post's text content and append a hyperlink back to facebook
        $fb_link = '<br><br><a href="http://www.facebook.com/'. $post_id .'"><i>View original post on Facebook</i></a>';
        $p['content'] = $message . $fb_link;
        return $p;
    }
}
?>
