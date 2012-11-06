<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Expires" content="Fri, Jan 01 1900 00:00:00 GMT">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Cache-Control" content="no-cache">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="Lang" content="en">
        <meta name="author" content="PBX_g33k">
        <meta http-equiv="Reply-to" content="info@yu-go.eu">
        <meta name="description" content="Small script that makes it possible for the end-user to record live streams from the ustream network. Usage of this script is questionable in some jurisdictions.">
        <meta name="keywords" content="">
        <meta name="creation-date" content="06/01/2011">
        <meta name="revisit-after" content="15 days">
        
        
<link rel="stylesheet" href="css/main.css" type="text/css" media="screen" />
        
        <script src="http://cdn.jquerytools.org/1.2.7/full/jquery.tools.min.js"></script>
        <title>Ustream Ripper v0.2 ~by PBX_g33k~ ~for #pswg~</title>
        <style>
            /* use a semi-transparent image for the overlay */
            #overlay {
                background-image:url(http://jquerytoolg.org/media/img/overlay/transparent.png);
                color:#efefef;
                height:450px;
            }
            /* container for external content. uses vertical scrollbar, if needed */
            div.contentWrap {
                background: white;
                height:441px;
                overflow-y:auto;
            }
        </style>
    </head>
    <body>
        <? if(isset($_POST['channel'])){ ?>
            <?
                require_once( 'amfphp/core/amf/app/Gateway.php');
                require_once( AMFPHP_BASE . 'amf/io/AMFSerializer.php');
                require_once( AMFPHP_BASE . 'amf/io/AMFDeserializer.php');
                include_once('ustreamrip.class.php');
                $rip = new Ustreamrip();
                $rip->Init();
                $rip->__set('_CHANNEL',$_POST['channel']);
                //  $rip->setChannel('dj-kentai-jcore-hardcore-mix');
                //$rip->setChannel($_POST['channel']);
                $data = $rip->getRTMPCommand();
            ?>
            Found <?php echo count($data);?> streams. Please try the bellow command(s):<br><br>
            <?
                foreach($data as $command)
                {
                    echo $command."<br><br>";
                };
            ?>
            <? }else{ ?>
            This is a small test. Please write the channel URI and press submit (or enter, whatever floats your boat).<br>
            <form action="" method="POST">
                <input type="text" name="channel" class="validate['required']">
                <button type="submit" value="submit">~Submit~</button>
            </form>
            <?}?>
    <div id="feedback-button" style="position: fixed;bottom: 10px;right: 10px;">
        <a href="feedback/index.php" rel="#overlay" style="text-decoration:none">
          <button type="button">Feedback</button>
        </a>
    </div>
    <div id="overlay">
      <!-- the external content is loaded inside this tag -->
      <div class="contentWrap"></div>
    </div>
    <script>
$(function() {

    // if the function argument is given to overlay,
    // it is assumed to be the onBeforeLoad event listener
    $("a[rel]").overlay({

        mask: 'grey',
        effect: 'apple',

        onBeforeLoad: function() {

            // grab wrapper element inside content
            var wrap = this.getOverlay().find(".contentWrap");

            // load the page specified in the trigger
            wrap.load(this.getTrigger().attr("href"));
        }

    });
});
</script>
</body>
</html>
