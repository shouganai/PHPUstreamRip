PHPUstreamRip
=============

PHP code to generate a rtmpdump command with all required parameters to rip Ustream streams.

Usage:

    The easiest way to test this class is by downloading this project, place it somewhere in
    your www/public_html folder and run index.php.

Todo:

    Add logging
    Add better error handling
    Add caching support (flat-file or database ((My)SQL(ite)))
    Improve URI handling
    Add code to get latest Flash version for RTMPDump command
    Add update script
        
Changelog

    2012-11-06: improved URI handling. Now works with full URIs also
    2012-11-05: first commit to GitHub
                replaced getters and setters with magic methods
    2012-07-XX: first public version
                rewrote major parts for OOP approach
                updated getRTMP to support ustream's new amf format
                added support for multiple rtmp uris
    2008-XX-XX: initial version 
                Never published, ran on nopan.web2sms.nl

Legal notice:

    This script generates a command that can be used to record online broadcasts, including
    copyrighted material. Some countries forbid recording of copyrighted material so using
    this script can be illegal in your country. Using this script is up to you, i take no
    responsibility and by using this script you agree that you know the risks and you
    take full responsibility when using this scipt.
    
    If you don't know if you're allowed to use this script by your local law, consult a
    law specialist.
    
    The usage of this script may not be permitted in some countries.
    I take no responsibility if you get in trouble for using this script.
    Usage, modification, distribution at YOUR OWN RISK!

    THIS SCRIPT IS PROVIDED "AS IS" WITHOUT WARRANTY OF ANY KIND, EITHER EXPRESSED  
    OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR  
    FITNESS FOR A PARTICULAR PURPOSE. 

    The script is provided AS IS without warranty of any kind. The author further disclaims all 
    implied warranties including, without limitation, any implied warranties of merchantability 
    or of fitness for a particular purpose. The entire risk arising out of the use or performance 
    of the sample and documentation remains with you. In no event shall its authors, 
    or anyone else involved in the creation, production, or delivery of the script be liable for  
    any damages whatsoever (including, without limitation, damages for loss of business profits,  
    business interruption, loss of business information, or other pecuniary loss) arising out of  
    the use of or inability to use the sample or documentation, even if the author has been advised  
    of the possibility of such damages. 