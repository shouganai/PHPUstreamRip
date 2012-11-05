<?php
  
?>
<?php
/** 
 * The class ustreamrip.class.php 
 *  
 * The Ustreamrip class provides an easy method to get commands for recording 
 * live ustream webcasts. Do note that the legality of this tool differs per
 * state/country and situation. Please consult your local professionals if
 * your allowed (by law) to use this tool. In my case it's allowed for 
 * personal usage.
 *  
 * @package Ustreamrip
 * @author Oguzhan Uysal <info@yu-go.eu>
 * @version 0.1.0
 * @todo 
 *  
 * History: 
 *     2012-07-XX: first public version
 *                 rewrote major parts for OOP approach
 *                 updated getRTMP to support ustream's new amf format
 *                 added support for multiple rtmp uris
 *     2008-XX-XX: initial version 
 *                 Never published, run on nopan.web2sms.nl
 */
 
 /*
 * 
 * TODO:
 * 
 * Add error codes
 * Add logging
 * Add caching support (flat-file or database)
 */
 
 /*
 *
 *  NOTE:
 * 
 *  This is a rewrite of a code i've written over two years ago. A lot of 
 *  the code is a mindfuck for me and i'm deeply ashamed of writing it.
 *  I don't know what some of the code does, so i've marked these functions
 *  as is. I will look into the code later and decypher it :3
 */
    class Ustreamrip
    {   
        var $_APIKEY;
        var $_CHANNEL;
        var $ustreamCDNHost = "cdngw.ustream.tv";
        var $ustreamCDNPath = "/Viewer/getStream/1/";
        var $ustreamHost = "www.ustream.tv";
        var $ustreamPath = "/channel/";
        var $ustreamAPIHost = "http://api.ustream.tv";
        var $amfData;
        var $rtmpData;
        var $amfObject;
        var $channelData;
        var $channelID;
        var $userID;
        var $command;
        var $status;
        
        function Ustreamrip()
        {
        }
        
        function Init()
        {
            $this->_APIKEY = ""; //Insert API-Key you can get from ustream here. http://developers.soundcloud.com/
        }
        /**
        * Returns the current APIKey
        * 
        */
        function getAPIKey()
        {
            return $this->_APIKEY;
        }
        
        /**
        * No need to use this function unless you use multiple keys.
        * 
        * @param string $key
        */
        function setAPIKey($key)
        {
            $this->_APIKEY = $key;
        }
        
        function setChannel($channel)
        {
            $this->_CHANNEL = $channel;
        }
        
        function getChannel()
        {
            return $this->_CHANNEL;
        }
        
        /**
        * Used to get all required files from ustream.
        * 
        * @param string $verb
        * @param string $ip
        * @param int $port
        * @param string $uri
        * @param array $getdata
        * @param array $postdata
        * @param array $cookie
        * @param array $custom_headers
        * @param int $timeout
        * @param mixed $req_hdr
        * @param mixed $res_hdr
        * @return none
        */
        function http_request($verb='GET',$ip,$port=80,$uri='/',$getdata=array(),$postdata=array(),$cookie=array(),$custom_headers=array(),$timeout=1000,$req_hdr=false,$res_hdr=false){
            $ret = '';
            $verb = strtoupper($verb);
            $cookie_str = '';
            $getdata_str = count($getdata) ? '?' : '';
            $postdata_str = '';

            foreach ($getdata as $k => $v)
                $getdata_str .= urlencode($k) .'='. urlencode($v);

            foreach ($postdata as $k => $v)
                $postdata_str .= urlencode($k) .'='. urlencode($v) .'&';

            foreach ($cookie as $k => $v)
                $cookie_str .= urlencode($k) .'='. urlencode($v) .'; ';

            $crlf = "\r\n";
            $req = $verb .' '. $uri . $getdata_str .' HTTP/1.1' . $crlf;
            $req .= 'Host: '. $ip . $crlf;
            $req .= 'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.7 (KHTML, like Gecko) RockMelt/0.16.91.478 Chrome/16.0.912.77 Safari/535.7' . $crlf;
//            $req .= 'User-Agent: Mozilla/5.0 Firefox/3.6.12' . $crlf;
            $req .= 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' . $crlf;
            $req .= 'Accept-Language: en-us,en;q=0.5' . $crlf;
            $req .= 'Accept-Encoding: deflate' . $crlf;
            $req .= 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7' . $crlf;
            
            var_dump($req);die();

            foreach ($custom_headers as $k => $v)
                $req .= $k .': '. $v . $crlf;

            if (!empty($cookie_str))
                $req .= 'Cookie: '. substr($cookie_str, 0, -2) . $crlf;

            if ($verb == 'POST' && !empty($postdata_str))
            {
                $postdata_str = substr($postdata_str, 0, -1);
                $req .= 'Content-Type: application/x-www-form-urlencoded' . $crlf;
                $req .= 'Content-Length: '. strlen($postdata_str) . $crlf . $crlf;
                $req .= $postdata_str;
            }
            else $req .= $crlf;

            if ($req_hdr)
                $ret .= $req;

            if (($fp = @fsockopen($ip, $port, $errno, $errstr)) == false)
                return "Error $errno: $errstr\n";

            stream_set_timeout($fp, 0, $timeout * 1000);

            fputs($fp, $req);
            while ($line = fgets($fp)) $ret .= $line;
            fclose($fp);

            if (!$res_hdr)
                $ret = substr($ret, strpos($ret, "\r\n\r\n") + 4);

            return $ret;
        }
         
        function getuStreamInfo(){
            $request =  $this->ustreamAPIHost;
            $format = 'php';   // this can be xml, json, html, or php. Keep it on php unless you like to hack.
            $args = 'subject=channel';
            $args .= "&uid=".$this->_CHANNEL;
            $args .= '&command=getInfo';
            $args .= '&key='.$this->_APIKEY; 
            var_dump($args);die();
            $session = curl_init($request.'/'.$format.'?'.$args);
            curl_setopt($session, CURLOPT_HEADER, false);
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($session);
            curl_close($session);

            $resultsArray = unserialize($response);
            $this->channelData = $resultsArray;
            $this->channelID = $resultsArray['results']['id'];
            $this->userID = $resultsArray['results']['user']['id'];
            $this->status = $resultsArray['results']['status'];
        }
        
        function getChannelStatus()
        {
            return $this->status;
            //NOT YET IMPLEMENTED
        }
        
        function getuStreamcron($channels=array()){
            //UNUSED FUNCTION LEFT FROM REWRITE. STILL DONT KNOW WHAT THIS EXACTLY DOES :3
            $req=null;
            foreach($channels as $chan){
                $req.=$chan.';';
            }
            $req=substr($req,0,-1);
            return($this->getuStreamInfo($req));
        }

        function getAMF(){
            $source=$this->http_request('GET',$this->ustreamCDNHost,80,$this->ustreamCDNPath.$this->_CHANNEL);
            preg_match("/Channel\s+ID\:\s+(\d+)/",$source,$return);
            $this->amfData = $return;
        }

        function getChannelDB(){
            //THIS FUNCTION DOES NOT DO ANYTHING WITHOUT THE DB. REQUIRES BACKUPS FROM THE SERVER. TODO!!
            require_once('sql.php');
            $r=execSQL("SELECT chanid,ownerid from `pswg`.`ustream` where `url` LIKE '%".$this->_CHANNEL."%' LIMIT 1",1);
            $re[0]=$r[0]['ownerid'];
            $re[1]=$r[0]['chanid'];
            return $re;
        }

        function getRTMP($chan=FALSE){
            $source = $this->http_request('GET',$this->ustreamCDNHost,80,$this->ustreamCDNPath.$this->channelID.".amf");

            $amf = new AMFObject($source);
            $deserializer = new AMFDeserializer($amf->rawData);
            $deserializer->deserialize($amf);
            $this->amfData = $deserializer->amfdata->_bodys;
            $this->amfData = $this->amfData[0];

            if(($this->status == "online" || $this->status == "live") && isset($this->amfData->_value['fmsUrl']))
            {
                // Stream uses a single stream server.
                // Return rtmpdump command and variabless
                // streamName is always "streams/live"
                $this->rtmpData = array();
                preg_match('~(((http|ftp|https|rtmp):/\/)|www\.)[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&:/\+#!]*[\w\-\@?^=%&\+#])?~',$this->amfData->_value['fmsUrl'],$m);
                $this->rtmpData[0] = array($this->amfData->_value['fmsUrl'],$this->amfData->_value['streamName'],substr($m[5],1));
            }
            elseif(($this->status == "online" || $this->status == "live") && isset($this->amfData->_value['cdnUrl']))
            {
                // Stream uses CDN to stream to clients
                // Start for loop iterating available providers (akamai, level3, etc)
                // and return rtmpdump command and variables for each stream.
                // NOTE: each server uses a seperate streamName, algorithm unknown as of now.
                
                $streamkeys = array_keys($this->amfData->_value['streamVersions']);
                foreach($this->amfData->_value['streamVersions'][$streamkeys[0]]['streamVersionCdn'] as $cdn)
                {
                    preg_match('~(((http|ftp|https|rtmp):/\/)|www\.)[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&:/\+#!]*[\w\-\@?^=%&\+#])?~',$cdn['cdnStreamUrl'],$m);
                    $this->rtmpData[] = array($cdn['cdnStreamUrl'],$cdn['cdnStreamName'],substr($m[5],1));
                }
            }
            elseif($this->status == "offline")
            {
                var_dump("CHANNEL OFFLINE!!!");
            }
            else
            {
                var_dump("UNKNOWN ERROR");
            }
        }
        
        function getEmbedSWF(){
            $source = $this->http_request('GET',$this->ustreamHost,80,$this->ustreamPath.$this->_CHANNEL);
            preg_match("(http://cdn(\d+)\.ustream\.tv/swf/(\d+)/viewer\.(\d+)\.swf)",$source,$return);
            $return['p'] = TRUE;
            return($return);
        }
        
        function getProtectedRTMP($chan){
            $source = $this->http_request('GET',$this->ustreamCDNHost,80,$this->ustreamCDNPath.$chan.".amf");
            preg_match("(rtmp://[^/]+/ustream/test)",$source,$return); #gets RTMP URI
            preg_match("(ustream@[^/]+1)",$source,$name); #GETS streamName (used as -A param for rtmpdump)
            $swf = $this->getEmbedSWF();
            $return['name'] = $name[0];
            $return['swf'] = $swf[0];
            $return['p'] = TRUE;
            if($return){
                $this->rtmpData = $return;
            }
        }
        
        function getRTMPCommand()
        {
            $this->getuStreamInfo();
            $this->getRTMP();
            $this->command = array();
            for($i=0;$i<count($this->rtmpData);$i++)
                $this->command[] = "rtmpdump -v -r ".$this->rtmpData[$i][0]." -a \"".$this->rtmpData[$i][2]."\" -f \"WIN 11,0,1,152\" -y \"".$this->rtmpData[$i][1]."\" -s \"http://static-cdn1.ustream.tv/swf/live/viewer.rsl:249.swf\" -o \"C:\\dump\\".$this->_CHANNEL.".flv\"";
            
            return $this->command;
        }
        
        function getRTMPCommandSingle(){
            $this->getRTMPCommand();
            return $this->command[0];
        }
    }
?>
