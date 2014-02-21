<?php
    /**
    * 
    * The class ustreamrip.class.php 
    *  
    * The Ustreamrip class provides an easy method to get commands for recording 
    * live ustream webcasts. Do note that the legality of this tool differs per
    * state/country and situation. Please consult your local professionals if
    * your allowed (by law) to use this tool. In my case it's allowed for 
    * personal usage.
    *  
    * @name Ustreamrip
    * @package Ustreamrip
    * @author PBX_g33k <phpripper@yu-go.eu>
    * @author Shouganai <git@shou.gan.ai>
    * @version 0.2.1
    * @todo Add logging
    * @todo Add better error handling
    * @todo Add caching support (flat-file or database ((My)SQL(ite)))
    *  
    * History: 
    *      2013-02-12: transferred ownership to shouganai on GitHub.
    *      2012-11-12: added support for recorded videos.
    *                  improved URI handling.
    *      2012-11-06: improved URI handling. Now works with full URIs also
    *      2012-11-05: first commit to GitHub
    *                  replaced getters and setters with magic methods
    *      2012-07-XX: first public version
    *                  rewrote major parts for OOP approach
    *                  updated getRTMP to support ustream's new amf format
    *                  added support for multiple rtmp uris
    *      2008-XX-XX: initial version 
    *                  Never published, ran on nopan.web2sms.nl
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
        var $recordedVideoHost = "tcdn.ustream.tv";
        var $recordedVideoPath = "/video/";
        var $rtmpDumpPath;
        var $outpuPath;
        var $amfData;
        var $rtmpData;
        var $amfObject;
        var $channelData;
        var $channelID;
        var $userID;
        var $command;
        var $status;
        var $channelType; // 0 = Livestream, 1 = Recorded

        function Ustreamrip($properties = array())
        {
            foreach($properties as $propertyKey => $propertyValue)
            {
                $this->__set($propertyKey,$propertyValue);
            }
        }

        function __get($property)
        {
            if(property_exists($this,$property))
            {
                return $this->$property;
            }
        }

        function __set($property,$value)
        {
            if(property_exists($this,$property))
            {
                $this->$property = $value;
            }
        }

        function Init()
        {
            $this->__set('rtmpDumpPath', ''); 
            $this->__set('outputPath', 'c:/dump/');
            //$this->__set('_APIKEY', '') //Insert API-Key you can get from ustream here. http://developer.ustream.tv/
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
            $req .= 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' . $crlf;
            $req .= 'Accept-Language: en-us,en;q=0.5' . $crlf;
            $req .= 'Accept-Encoding: deflate' . $crlf;
            $req .= 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7' . $crlf;

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
            $this->getChannelType();
            if($this->channelType == 1)
            {
                return;
            }
            $this->processChannelName();
            $request =  $this->ustreamAPIHost;
            $format = 'php';   // this can be xml, json, html, or php. Keep it on php unless you like to hack.
            $args = 'subject=channel&uid='.$this->__get('_CHANNEL');
            $args .= '&command=getInfo&key='.$this->__get('_APIKEY');
            $session = curl_init($request.'/'.$format.'?'.$args);
            curl_setopt($session, CURLOPT_HEADER, false);
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($session);
            curl_close($session);

            $resultsArray = unserialize($response);
            $this->__set('channelData', $resultsArray);
            $this->__set('channelID', $resultsArray['results']['id']);
            $this->__set('userID', $resultsArray['results']['user']['id']);
            $this->__set('userID', $resultsArray['results']['status']);
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
            $this->status = $this->amfData->_value['status'];

            if(($this->status == "online" || $this->status == "live") && isset($this->amfData->_value['cdnUrl']))
            {
                if ($this->amfData->_value['streamVersions'])
                {
                    // Stream uses CDN to stream to clients
                    // Start for loop iterating available providers (akamai, level3, etc)
                    // and return rtmpdump command and variables for each stream.
                    // NOTE: each server uses a seperate streamName, algorithm unknown as of now.

                    $streamkeys = array_keys($this->amfData->_value['streamVersions']);
                    foreach($this->amfData->_value['streamVersions'][$streamkeys[0]]['streamVersionCdn'] as $cdn)
                    {
                        $m = $this->extractRTMPURI($cdn['cdnStreamUrl']);
                        $this->rtmpData[] = array($cdn['cdnStreamUrl'],$cdn['cdnStreamName'],substr($m[5],1));
                    }
                }
                else
                {
                    // 2012-12-22, simple fmsUrl like cdn stream
                    $m = $this->extractRTMPURI($this->amfData->_value['cdnUrl']);
                    $this->rtmpData[] = array($this->amfData->_value['cdnUrl'],$this->amfData->_value['streamName'],substr($m[5],1));
                }
            }
            elseif(($this->status == "online" || $this->status == "live") && isset($this->amfData->_value['fmsUrl']))
            {
                // Stream uses a single stream server.
                // Return rtmpdump command and variabless
                $this->rtmpData = array();
                $m = $this->extractRTMPURI($this->amfData->_value['fmsUrl']);
                $this->rtmpData[0] = array($this->amfData->_value['fmsUrl'],$this->amfData->_value['streamName'],substr($m[5],1));
            }
            elseif($this->status == "offline")
            {
                var_dump($this->__get('_CHANNEL'),"CHANNEL OFFLINE!!!");
            }
        }

        function extractRTMPURI($uri)
        {
            preg_match('~(((http|ftp|https|rtmp):/\/)|www\.)[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&:/\+#!]*[\w\-\@?^=%&\+#])?~',$uri,$m);
            return $m;
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

            if($this->channelType == 1)
                return $this->getRecordedVideoUrl();

            $this->getRTMP();
            $this->command = array();
            for($i=0;$i<count($this->rtmpData);$i++)
                $this->command[] = $this->rtmpDumpPath."rtmpdump -v -r ".$this->rtmpData[$i][0]." -a \"".$this->rtmpData[$i][2]."\" -f \"WIN 11,0,1,152\" -y \"".$this->rtmpData[$i][1]."\" -s \"http://static-cdn1.ustream.tv/swf/live/vieweri.rsl:249.swf\" -o \"".$this->outpuPath.$this->_CHANNEL.".flv\"";

            return $this->command;
        }

        function getChannelType()
        {
            if(strpos($this->__get('_CHANNEL'),'recorded'))
                $this->__set('channelType',1);
            else
                $this->__set('channelType',0); 
        }

        function getVideoID()
        {
            $parts = explode('/',$this->__get('_CHANNEL'));
            $chanid = $parts[count($parts)-1];
            if(strpos($chanid,'?'))
                return intval(substr($chanid,0,strlen($chanid) - strpos($chanid,'?')));
            elseif(strpos($chanid,'#'))
                return intval(substr($chanid,0,strlen($chanid) - strpos($chanid,'#')));
            else
                return intval($chanid);
        }

        function getRecordedVideoUrl()
        {
            return "http://{$this->recordedVideoHost}{$this->recordedVideoPath}{$this->getVideoID()}";
        }

        function processChannelName()
        {
            $uris = explode('/',$this->__get('_CHANNEL'));
            $amount = count($uris);
            $this->__set('_CHANNEL',$uris[$amount-1]);
        }

        function getRTMPCommandSingle(){
            $this->getRTMPCommand();
            return $this->command[0];
        }
    }
?>
