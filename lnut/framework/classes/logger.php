<?php

class logger {

     public static function run( $is_login_entry = 0 ) {
        if(isset($_SESSION) && is_array($_SESSION) && count($_SESSION) > 0) {
            $session_id = session_id();
        } else {
            $session_id = 0;
        }

        if(isset($_SESSION['user']) && isset($_SESSION['user']['uid'])) {
            $user_uid = $_SESSION['user']['uid'];
        
        /**
         * Log the user access
         */
        
		   $encoding           = (isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '');
		   $language           = (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '');
		   $browser            = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
		   $remoteaddress      = $_SERVER['REMOTE_ADDR'];
		 //  $remoteresolved     = gethostbyaddr($remoteaddress);
		   $requesteduri       = $_SERVER['REQUEST_URI'];
		   $requesttime        = date('Y-m-d H:i:s',$_SERVER['REQUEST_TIME']);
		   $referrer           = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		//   $keywords           = self::extractkeywords($referrer);
	
		   $log = new logging_access();
		   $log->set_sid($session_id);        
		   $log->set_user_uid($user_uid);
		   $log->set_uri($requesteduri);
		   $log->set_encoding($encoding);
		   $log->set_language($language);
		   $log->set_browser($browser);
		   $log->set_remoteaddress($remoteaddress);
//		   $log->set_remoteresolved($remoteresolved);
		   $log->set_time($requesttime);
		   $log->set_referrerurl($referrer);
		  // $log->set_keywords(implode(",",$keywords['keywords']));
		  // $log->set_searchengine($keywords['searchengine']);
		   $log->set_is_login_entry($is_login_entry);
		   if(isset($_SESSION['user']['school_uid'])) {
			   $log->set_school_uid($_SESSION['user']['school_uid']);			   
		   }		  
		   $log->insert();
	   
	   	   // UPDATES USER LAST_SEEN WITH CURRENT TIME TO WE CAN IDENTIFY USER IS SEEN LATELY.
	   	   $query = "UPDATE `user` SET `last_seen` = '".time()."' WHERE `uid` = '".$user_uid."'";
		   database::query($query);
	   
	   }
        else {
            $user_uid = 0;
        }

    }

    public static function extractkeywords($referrer='') {

        $searchengine	= '';
        $keywords		= array ();

        $ses=array(
                '4Anything'		=>array( 'url'=>'app.4anything.com', 'query'=>'query'),
                '7Metasearch'           =>array( 'url'=>'7metasearch.com', 'query'=>'q'),
                '7Search'		=>array( 'url'=>'7search.com', 'query'=>'qu'),
                'About'			=>array( 'url'=>'about.com', 'query'=>'terms'),
                'AllTheWeb'		=>array( 'url'=>'alltheweb.com', 'query'=>'query'),

                // Altavista
                'Altavista Austria'=>array( 'url'=>'at.altavista.com', 'query'=>'q'),
                'Altavista Australia'=>array( 'url'=>'au.altavista.com', 'query'=>'q'),
                'Altavista Belgium (French)'=>array( 'url'=>'be-fr.altavista.com', 'query'=>'q'),
                'Altavista Belgium (Dutch)'=>array( 'url'=>'be-nl.altavista.com', 'query'=>'q'),
                'Altavista Brazil'=>array( 'url'=>'br.altavista.com', 'query'=>'q'),
                'Altavista Canada (English)'=>array( 'url'=>'ca-en.altavista.com', 'query'=>'q'),
                'Altavista Canada (French)'=>array( 'url'=>'ca-fr.altavista.com', 'query'=>'q'),
                'Altavista Switzerland (German)'=>array('url'=>'ch-de.altavista.com', 'query'=>'q'),
                'Altavista Switzerland (French)'=>array('url'=>'ch-fr.altavista.com', 'query'=>'q'),
                'Altavista Czech Republic'=>array( 'url'=>'cz.altavista.com', 'query'=>'p'),
                'Altavista Germany'=>array( 'url'=>'de.altavista.com', 'query'=>'p'),
                'Altavista Denmark'=>array( 'url'=>'dk.altavista.com', 'query'=>'p'),
                'Altavista Spain'=>array( 'url'=>'es-es.altavista.com', 'query'=>'q'),
                'Altavista Finland'=>array( 'url'=>'fi.altavista.com', 'query'=>'q'),
                'Altavista France'=>array( 'url'=>'fr.altavista.com', 'query'=>'q'),
                'Altavista Health'=>array( 'url'=>'health.altavista.com', 'query'=>'q'),
                'Altavista Hungary'=>array( 'url'=>'hu.altavista.com', 'query'=>'q'),
                'Altavista Ireland'=>array( 'url'=>'ie.altavista.com', 'query'=>'q'),
                'Altavista India'=>array( 'url'=>'in.altavista.com', 'query'=>'q'),
                'Altavista Italy'=>array( 'url'=>'it.altavista.com', 'query'=>'q'),
                'Altavista Korea (South)'=>array( 'url'=>'kr.altavista.com', 'query'=>'q'),
                'Altavista Mexico'=>array( 'url'=>'mx.altavista.com', 'query'=>'q'),
                'Altavista Netherlands'=>array( 'url'=>'nl.altavista.com', 'query'=>'q'),
                'Altavista Norway'=>array( 'url'=>'no.altavista.com', 'query'=>'q'),
                'Altavista New Zealand'=>array( 'url'=>'nz.altavista.com', 'query'=>'q'),
                'Altavista Portugal'=>array( 'url'=>'pt.altavista.com', 'query'=>'q'),
                'Altavista Sweden'=>array( 'url'=>'se.altavista.com', 'query'=>'q'),
                'Altavista United Kingdom'=>array( 'url'=>'uk.altavista.com', 'query'=>'q'),
                'Altavista Listings'=>array( 'url'=>'listings.altavista.com','query'=>'q'),
                'Altavista Raging Search'=>array( 'url'=>'ragingsearch.altavista.com','query'=>'q'),
                'Altavista'=>array( 'url'=>'altavista.com', 'query'=>'q'),
                'Altavista Poland'=>array( 'url'=>'altavista.onet.pl', 'query'=>'q'),

                // AOL
                'AOL'=>array( 'url'=>'aolsearch.aol.com', 'query'=>'query'),
                'AOL Tv'=>array( 'url'=>'aoltvsearch.aol.com', 'query'=>'query'),
                'AOL Australia'=>array( 'url'=>'aolsearch.aol.com.au', 'query'=>'query'),
                'AOL Brasil'=>array( 'url'=>'aolbusca.aol.com.br', 'query'=>'query'),
                'AOL Canada'=>array( 'url'=>'aolsearch.aol.ca', 'query'=>'query'),
                'AOL Japan'=>array( 'url'=>'aol.goo.ne.jp', 'query'=>'MT'),
                'AOL UK'=>array( 'url'=>'aolsearch.aol.co.uk', 'query'=>'query'),
                'AOL Netfind'=>array( 'url'=>'aolfind.aol.com', 'query'=>'query'),
                'AOL Netfind Germany'=>array( 'url'=>'aolfind.aol.de', 'query'=>'query'),

                // Ask Jeeves
                'Ask Jeeves UK'=>array( 'url'=>'askjeeves.co.uk', 'query'=>'ask'),
                'Ask Jeeves'=>array( 'url'=>'askjeeves.com', 'query'=>'ask'),
                'Ask Jeeves Kids'=>array( 'url'=>'askjeeveskids.co.uk', 'query'=>'ask'),
                'Atomica'=>array( 'url'=>'lookup.atomica.com', 'query'=>'s'),
                'ATT News'=>array( 'url'=>'dailynews.att.net', 'query'=>'qry'),
                'Austria'=>array( 'url'=>'finder.austria.com', 'query'=>'query'),
                'Bellsouth'=>array( 'url'=>'home.bellsouth.net', 'query'=>'Keywords',
                        'query2'=>'string'),
                'Blueyonder'=>array( 'url'=>'bbsearch.blueyonder.co.uk','query'=>'q'),
                'Bol (Brazil)'=>array( 'url'=>'miner.bol.com.br', 'query'=>'q'),
                'BlueWin Switzerland'=>array( 'url'=>'av-de.bluewin.ch', 'query'=>'q'),
                'BZZ Russia'=>array( 'url'=>'bzz.ru', 'query'=>'query'),
                'Catloga (France)'=>array( 'url'=>'fr.web.caloga.com', 'query'=>'q'),
                'Chopstix'=>array( 'url'=>'au.chopstix.net', 'query'=>'search'),
                'Compuserve'=>array( 'url'=>'cissearch.compuserve.com','query'=>'sTerm'),
                'Curryguide'=>array( 'url'=>'fastsearch.curryguide.com','query'=>'query'),

                // DirectHit
                'DirectHit/Ask Jeeves'=>array( 'url'=>'askuk.directhit.com','query'=>'qry'),
                'DirectHit/Britannica'=>array( 'url'=>'britannica.directhit.com','query'=>'qry'),
                'DirectHit/Comet'=>array( 'url'=>'comet.directhit.com', 'query'=>'qry'),
                'DirectHit/Dictionary'=>array( 'url'=>'dictionary.directhit.com','query'=>'qry'),
                'DirectHit/Megadirectory'=>array( 'url'=>'megadirectory.directhit.com','query'=>'qry'),
                'DirectHit/MSN'=>array( 'url'=>'msn.directhit.com', 'query'=>'qry'),
                'DirectHit/Systema'=>array( 'url'=>'systema.directhit.com', 'query'=>'qry'),
                'DirectHit/Webster'=>array( 'url'=>'webster.directhit.com', 'query'=>'qry'),
                'DirectHit'=>array( 'url'=>'directhit.com', 'query'=>'qry'),
                'Open Directory Project'=>array( 'url'=>'dmoz.org', 'query'=>'search'),
                'Dogpile'=>array( 'url'=>'dogpile.com', 'query'=>'q'),
                'Dreamx'=>array( 'url'=>'m3.dreamx.net', 'query'=>'query'),
                'Earthlink'=>array( 'url'=>'goto.earthlink.net', 'query'=>'Keywords'),
                'Education World'=>array( 'url'=>'db.education-world.com','query'=>'queryText'),
                'Elsitio'=>array( 'url'=>'buscador.elsitio.com', 'query'=>'palabras'),
                'Elmundo Spain'=>array( 'url'=>'ariadna.elmundo.es', 'query'=>'q'),
                'Espotting'=>array( 'url'=>'affiliate.espotting.com','query'=>'keyword'),
                'Eureka'=>array( 'url'=>'eureka.com', 'query'=>'q'),

                // Excite
                'Excite Austria'=>array( 'url'=>'excite.at', 'query'=>'search'),
                'Excite Australia'=>array( 'url'=>'excite.com.au', 'query'=>'search'),
                'Excite Chinese'=>array( 'url'=>'excite.ch', 'query'=>'search'),
                'Excite Canada'=>array( 'url'=>'excite.ca', 'query'=>'search'),
                'Excite Germany'=>array( 'url'=>'excite.de', 'query'=>'search'),
                'Excite Denmark'=>array( 'url'=>'excite.dk', 'query'=>'search'),
                'Excite Spain'=>array( 'url'=>'excite.es', 'query'=>'search'),
                'Excite France'=>array( 'url'=>'excite.fr', 'query'=>'search'),
                'Excite Italy'=>array( 'url'=>'excite.it', 'query'=>'search'),
                'Excite Japan'=>array( 'url'=>'excite.co.jp', 'query'=>'search'),
                'Excite UK'=>array( 'url'=>'excite.co.uk', 'query'=>'search'),
                'Findia'=>array( 'url'=>'findia.net', 'query'=>'query'),
                'Go/Infoseek'=>array( 'url'=>'infoseek.go.com', 'query'=>'qt'),

                // Google
                'Google Directory'=>array( 'url'=>'directory.google.com', 'query'=>'q'),
                'Google/Gotonet'=>array( 'url'=>'gotonet.google.com', 'query'=>'q'),
                'Google Groups'=>array( 'url'=>'groups.google.com', 'query'=>'q'),
                'Google Images'=>array( 'url'=>'images.google.com', 'query'=>'q'),
                'Google'=>array( 'url'=>'google.com', 'query'=>'q',
                        'query2'=>'query'),
                'Google WAP'=>array( 'url'=>'wap.google.com', 'query'=>'q',
                        'query2'=>'query'),
                'Google (Austria)'=>array( 'url'=>'google.at', 'query'=>'q'),
                'Google (United Arab Emirates)'=>array('url'=>'google.ae', 'query'=>'q'),
                'Google (Belgium)'=>array( 'url'=>'google.be', 'query'=>'q'),
                'Google (Canada)'=>array( 'url'=>'google.ca', 'query'=>'q'),
                'Google (Cocos (Keeling) Islands)'=>array('url'=>'google.cc', 'query'=>'q'),
                'Google (Switzerland)'=>array( 'url'=>'google.ch', 'query'=>'q'),
                'Google (Costa Rica)'=>array( 'url'=>'google.co.cr', 'query'=>'q'),
                'Google (Israel)'=>array( 'url'=>'google.co.il', 'query'=>'q'),
                'Google (Japan)'=>array( 'url'=>'google.co.jp', 'query'=>'q'),
                'Google (South Korea)'=>array( 'url'=>'google.co.kr', 'query'=>'q'),
                'Google (New Zealand)'=>array( 'url'=>'google.co.nz', 'query'=>'q'),
                'Google (Thailand)'=>array( 'url'=>'google.co.th', 'query'=>'q'),
                'Google (United Kingdom)'=>array( 'url'=>'google.co.uk', 'query'=>'q'),
                'Google (Argentina)'=>array( 'url'=>'google.com.ar', 'query'=>'q'),
                'Google (Brasil)'=>array( 'url'=>'google.com.br', 'query'=>'q'),
                'Google (Greece)'=>array( 'url'=>'google.com.gr', 'query'=>'q'),
                'Google (Poland)'=>array( 'url'=>'google.com.pl', 'query'=>'q'),
                'Google (Russia)'=>array( 'url'=>'google.com.ru', 'query'=>'q'),
                'Google (Germany)'=>array( 'url'=>'google.de', 'query'=>'q'),
                'Google (Micronesia)'=>array( 'url'=>'google.fm', 'query'=>'q'),
                'Google (France)'=>array( 'url'=>'google.fr', 'query'=>'q'),
                'Google (Ireland)'=>array( 'url'=>'google.ie', 'query'=>'q'),
                'Google (Italy)'=>array( 'url'=>'google.it', 'query'=>'q'),
                'Google (Liechtenstein)'=>array( 'url'=>'google.li', 'query'=>'q'),
                'Google (Lithuania)'=>array( 'url'=>'google.lt', 'query'=>'q'),
                'Google (Latvia)'=>array( 'url'=>'google.lv', 'query'=>'q'),
                'Google (Netherlands)'=>array( 'url'=>'google.nl', 'query'=>'q'),
                'Google (Portugal)'=>array( 'url'=>'google.pt', 'query'=>'q'),
                'Google (Portugal/English)'=>array( 'url'=>'pesquisa.google.pt', 'query'=>'q'),
                'Google (British Virgin Islands)'=>array('url'=>'google.vg', 'query'=>'q'),
                'Goto'=>array( 'url'=>'goto.com', 'query'=>'Keywords'),
                'Hananet'=>array( 'url'=>'dxm4.hananet.net', 'query'=>'query'),
                'Hanafos'=>array( 'url'=>'hanafos.com', 'query'=>'query'),
                'Hispatvista'=>array( 'url'=>'buscar.hispavista.com', 'query'=>'cadena'),
                'HotBot'=>array( 'url'=>'click.hotbot.com', 'query'=>'query'),
                'ICQ/Google'=>array( 'url'=>'google.icq.com', 'query'=>'q'),
                'Infospace'=>array( 'url'=>'dpxml.infospace.com', 'query'=>'qkw'),
                'Infospace'=>array( 'url'=>'kevxml.infospace.com', 'query'=>'qkw'),
                'LinkSynergy'=>array( 'url'=>'click.linksynergy.com', 'query'=>'q'),
                'LookSmart (CNN)'=>array( 'url'=>'cnn.looksmart.com', 'query'=>'key'),
                'LookSmart (CNNFN)'=>array( 'url'=>'cnnfn.looksmart.com', 'query'=>'key'),

                // Lycos
                'Lycos'=>array( 'url'=>'home.lycos.com', 'query'=>'query'),
                'Lycos/Hotbot Directory'=>array( 'url'=>'dir.hotbot.lycos.com', 'query'=>'MT'),
                'Lycos/Hotbot'=>array( 'url'=>'hotbot.lycos.com', 'query'=>'MT',
                        'query2'=>'query'),
                'Lycos/Lycospro'=>array( 'url'=>'lycospro.lycos.com', 'query'=>'query'),
                'LycosAsia India'=>array( 'url'=>'in.lycosasia.com', 'query'=>'query'),
                'LycosAsia Malaysia'=>array( 'url'=>'my.lycosasia.com', 'query'=>'query'),
                'Lycos Italy'=>array( 'url'=>'cerca.lycos.it', 'query'=>'query'),
                'Lycos Spain'=>array( 'url'=>'buscador.lycos.es', 'query'=>'query'),
                'Lycos/Google Korea (South)'=>array( 'url'=>'google.lycos.co.kr', 'query'=>'q'),
                'Lycos Russia'=>array( 'url'=>'poisk.lycos.co.ru', 'query'=>'query'),
                'Mamma'=>array( 'url'=>'mamma.com', 'query'=>'query'),
                'Metacrawler'=>array( 'url'=>'metacrawler.com', 'query'=>'general'),
                'Mirago'=>array( 'url'=>'mirago.com', 'query'=>'qry'),
                'MSN Search'=>array( 'url'=>'search.msn.com', 'query'=>'q'),
                'MSN Canada (French)'=>array( 'url'=>'fr.ca.search.msn.com', 'query'=>'q'),
                'MSN'=>array( 'url'=>'search.msn.com', 'query'=>'q'),
                'MyNet'=>array( 'url'=>'arama.mynet.com', 'query'=>'q'),
                'NANA Israel'=>array( 'url'=>'crawler.nana.co.il', 'query'=>'string'),
                'NANA Israel (English)'=>array( 'url'=>'english.nana.co.il', 'query'=>'string'),
                'Naver'=>array( 'url'=>'meta.naver.com', 'query'=>'query',
                        'query2'=>'oldquery'),
                'NBCI'=>array( 'url'=>'nbci.com', 'query'=>'keyword'),
                'NetBul'=>array( 'url'=>'kapi.netbul.com', 'query'=>'keyword'),

                // Netscape
                'Netscape/Excite Australia'=>array( 'url'=>'excite.au.netscape.com','query'=>'search'),
                'Netscape/Excite Germany'=>array( 'url'=>'excite.de.netscape.com','query'=>'search'),
                'Netscape/Excite France'=>array( 'url'=>'excite.fr.netscape.com','query'=>'search'),
                'Netscape/Excite UK'=>array( 'url'=>'excite.uk.netscape.com','query'=>'search'),
                'Netscape (Directory)'=>array( 'url'=>'directory.netscape.com','query'=>'search'),
                'Netscape/Google'=>array( 'url'=>'google.netscape.com', 'query'=>'query,q'),
                'Netscape NetBusiness'=>array( 'url'=>'netbusiness.netscape.com',
                        'query'=>'searchString'),
                'Netscape'=>array( 'url'=>'netscape.com', 'query'=>'search'),
                'Netscape Online'=>array( 'url'=>'netscapeonline.co.uk', 'query'=>'query'),
                'Northernlight'=>array( 'url'=>'northernlight.com', 'query'=>'qr'),
                'Nomade France'=>array( 'url'=>'ink.nomade.fr', 'query'=>'MT'),
                'Search.com'=>array( 'url'=>'search.com', 'query'=>'q'),
                'Searchopolis'=>array( 'url'=>'searchopolis.com', 'query'=>'request'),
                'Snap'=>array( 'url'=>'snap.com', 'query'=>'keyword'),
                'StarMedia'=>array( 'url'=>'buscaweb.starmedia.com','query'=>'query'),
                'Superonline'=>array( 'url'=>'arama.superoneline.com','query'=>'query'),
                'Spray/Lycos Sweden'=>array( 'url'=>'lycos.spray.se', 'query'=>'query'),
                'Spray/Lycossvar Sweden'=>array( 'url'=>'lycossvar.spray.se', 'query'=>'query'),
                'Suomi24 Finland'=>array( 'url'=>'evreka.suomi24.fi', 'query'=>'q'),
                'Terra (Brasil)'=>array( 'url'=>'busca.terra.com.br', 'query'=>'query'),
                'Terra (Spain)'=>array( 'url'=>'buscador.terra.es', 'query'=>'Claus',
                        'query2'=>'query'),
                'Tiscalinet'=>array( 'url'=>'janas.tiscalinet.it', 'query'=>'query'),
                'T-Online (Germany)'=>array( 'url'=>'brisbane.t-online.de', 'query'=>'q'),
                'UOL Argentina'=>array( 'url'=>'buscar.uol.com.ar', 'query'=>'q'),
                'Virgilio/Google'=>array( 'url'=>'virgilio.it', 'query'=>'qs',
                        'extra'=>'db=gg'),
                'Virgilio/Altavista'=>array( 'url'=>'virgilio.it', 'query'=>'qs',
                        'extra'=>'db=av'),
                'Virgilio'=>array( 'url'=>'virgilio.it', 'query'=>'qs'),
                'Ya'=>array( 'url'=>'buscador.ya.com', 'query'=>'item'),

                // Yahoo
                'Yahoo/Busca Brasil'=>array( 'url'=>'br.busca.yahoo.com', 'query'=>'p'),
                'Yahoo/Google Argentina'=>array( 'url'=>'ar.google.yahoo.com', 'query'=>'p'),
                'Yahoo/Google Asia'=>array( 'url'=>'asia.google.yahoo.com', 'query'=>'p'),
                'Yahoo/Google Brasil'=>array( 'url'=>'br.google.yahoo.com', 'query'=>'p'),
                'Yahoo/Google Canada'=>array( 'url'=>'ca.google.yahoo.com', 'query'=>'p'),
                'Yahoo/Google China'=>array( 'url'=>'cn.google.yahoo.com', 'query'=>'p'),
                'Yahoo/Google Germany'=>array( 'url'=>'de.google.yahoo.com', 'query'=>'p'),
                'Yahoo/Google Denmark'=>array( 'url'=>'dk.google.yahoo.com', 'query'=>'p'),
                'Yahoo/Google Spain'=>array( 'url'=>'es.google.yahoo.com', 'query'=>'p'),
                'Yahoo/Google France'=>array( 'url'=>'fr.google.yahoo.com', 'query'=>'p'),
                'Yahoo/Google Hong Kong (English)'=>array('url'=>'hke.google.yahoo.com','query'=>'p'),
                'Yahoo/Google India'=>array( 'url'=>'in.google.yahoo.com', 'query'=>'p'),
                'Yahoo/Google Italy'=>array( 'url'=>'it.google.yahoo.com', 'query'=>'p'),
                'Yahoo/Google Mexico'=>array( 'url'=>'mx.google.yahoo.com', 'query'=>'p'),
                'Yahoo/Google Sweden'=>array( 'url'=>'se.google.yahoo.com', 'query'=>'p'),
                'Yahoo/Google Singapore'=>array( 'url'=>'sg.google.yahoo.com', 'query'=>'p'),
                'Yahoo/Google United Kingdom'=>array( 'url'=>'uk.google.yahoo.com', 'query'=>'p'),
                'Yahoo Search Asia'=>array( 'url'=>'asia.search.yahoo.com', 'query'=>'p'),
                'Yahoo Search Australia'=>array( 'url'=>'au.search.yahoo.com', 'query'=>'p'),
                'Yahoo Search Canada'=>array( 'url'=>'ca.search.yahoo.com', 'query'=>'p'),
                'Yahoo Search Germany'=>array( 'url'=>'de.search.yahoo.com', 'query'=>'p'),
                'Yahoo Search Denmark'=>array( 'url'=>'dk.search.yahoo.com', 'query'=>'p'),
                'Yahoo Search France'=>array( 'url'=>'fr.search.yahoo.com', 'query'=>'p'),
                'Yahoo Search India'=>array( 'url'=>'in.search.yahoo.com', 'query'=>'p'),
                'Yahoo Search Mexico'=>array( 'url'=>'mx.search.yahoo.com', 'query'=>'p'),
                'Yahoo Search Norway'=>array( 'url'=>'no.search.yahoo.com', 'query'=>'p'),
                'Yahoo Search Sweden'=>array( 'url'=>'se.search.yahoo.com', 'query'=>'p'),
                'Yahoo Search Singapore'=>array( 'url'=>'sg.search.yahoo.com', 'query'=>'p'),
                'Yahoo Search United Kingdom'=>array( 'url'=>'uk.search.yahoo.com', 'query'=>'p'),
                'Yahoo/Google'=>array( 'url'=>'google.yahoo.com', 'query'=>'p'),
                'Yahoo Chinese'=>array( 'url'=>'chinese.yahoo.com', 'query'=>'p'),
                'Yahoo Spanish'=>array( 'url'=>'espanol.yahoo.com', 'query'=>'p'),
                'Yahoo Advanced Search'=>array( 'url'=>'av.yahoo.com', 'query'=>'p'),
                'Yahoo Inktomi Singapore'=>array( 'url'=>'sg.ink.yahoo.com', 'query'=>'p'),
                'Yahoo Inktomi United Kingdom'=>array( 'url'=>'uk.ink.yahoo.com', 'query'=>'p'),
                'Yahoo Inktomi'=>array( 'url'=>'ink.yahoo.com', 'query'=>'p'),
                'Yahoo'=>array( 'url'=>'yahoo.com', 'query'=>'p'),
                'Weather underground'=>array( 'url'=>'autobrand.wunderground.com',
                        'query'=>'q,search'),
                'Weather underground (Dutch)'=>array( 'url'=>'dutch.wunderground.com',
                        'query'=>'search'),
                'Walla Israel'=>array( 'url'=>'find.walla.co.il', 'query'=>'q'),
                'Web Agri (France)'=>array( 'url'=>'moteur.web-agri.fr', 'query'=>'SearchString'),
                'WWW Finland'=>array( 'url'=>'haku.www.fi', 'query'=>'qt,w'),
                'WWW Finland'=>array( 'url'=>'haku2.wwww.fi', 'query'=>'word'),
        );
        $replacements=array(
                // Replace this host With this one
                '204\.152\.166\.41' => 'mamma.com',
                '204\.152\.166\.43' => 'mamma.com',
                '208\.49\.237\.73' => 'mamma.com',
                '64\.210\.177\.12' => 'mamma.com',
                '205\.178\.174\.151' => 'one2seek.com',
                '206\.132\.152\.250' => 'goto.com',
                '216\.15\.219\.40' => 'metacrawler.de',
                '216\.200\.119\.160' => 'ask.co.uk',
                '216\.239\.[0-9]+\.[0-9]+' => 'google.com',
                'go[o]?[o]?gle\.[net|org]' => 'google.com',
                '[googil|goolge|wwwgoogle]\.com'=> 'google.com',
                '216\.34\.146\.167:8000' => 'indiatimes.com',
                '216\.46\.233\.50' => 'netbul.com',
                '62\.0\.45\.51' => 'mfa.gov.il',
                'aj\.com' => 'askjeeves.com',
                'ajkids.com' => 'askjeeveskids.com',
                'askgeeves.com' => 'askjeeves.com',
                'askjeevs.com' => 'askjeeves.com',
                'ask.com' => 'askjeeves.com',
                'ask.co.uk' => 'askjeeves.co.uk',
                'av\.com' => 'altavista.com',
                'alta-vista.com' => 'altavista.com',
                'altavista.com.mx' => 'mx.altavista.com',
                'altavista.de' => 'de.altavista.com',
                'altavista.cz' => 'cz.altavista.com',
                'altavista.hu' => 'hu.altavista.com',
                'go2\.com' => 'goto.com',
                'results.*\.profusion\.com' => 'results.profusion.com',
                's[0-9]+\.ixquick\.com' => 's1.ixquick.com',
                'search.*\.virgilio\.it' => 'search.virgilio.it',
                'search2\.cometsystems\.com' => 'search.cometsystems.com',
                'www.*\.savvysearch\.com' => 'www.savvysearch.com',
                'start.co.il:[0-9]+' => 'start.co.il',
                'www2.links2go.com' => 'links2go.com',
                'ww3.evreka.com' => 'www.evreka.com',
                'fr.excite.com' => 'excite.fr',
                'chinese.excite.com' => 'excite.ch',
        );

        if (preg_match('/^(.*)\?(.*)/',$referrer,$regs)) {

            $refhost	= $regs[1];
            $refquery	= $regs[2];

            foreach ($replacements as $key => $value) {
                if (preg_match("/^http:\/\/(www)?".$key."\//",$refhost)) {
                    $refhost="http://".$value."/";
                    break;
                }
            }
            $sechecked=0;
            foreach($ses as $name=>$seng) {
                $sechecked++;
                if (preg_match('/'.$seng['url']."/",$refhost)) {
                    if (isset($seng['home'])) {
                        // if there is a 'set homepage' for the SE, store that
                        $searchengineurl="http://".$seng['home']."/";
                    } else {
                        // other wise 'build' the SE's URL based on the search URL
                        $searchengineurl="http://www.".$seng['url']."/";
                    }
                    if (isset($seng['extra'])) {
                        //any extra parameters to check ?
                        if (!preg_match('/'.$seng['extra'].'/Ui',$refquery)) {
                            continue;
                        }
                    }
                    if (preg_match('/'.$seng['query'].'=(.*)(?:&|$)/Ui',$refquery,$regs)) {
                        // ok, we have a match!
                        $searched=urldecode($regs[1]);
                        $keywords=$searched;
                        $keywords=explode(' ',$searched); // split up the keywords
                        $searchengine=$name; // store the name of the search engine
                    } else {
                        if (isset($seng["query2"])) {
                            if (preg_match('/'.$seng['query2'].'=(.*)(?:&|$)/Ui',$refquery,$regs)) {
                                // ok, we have a match!
                                $searched=urldecode($regs[1]);
                                $keywords=$searched;
                                $keywords=explode(' ',$searched); // split up the keywords
                                $searchengine=$name; // store the name of the search engine
                            }
                        }
                    }
                    break;
                }
            }
        }

        return array (
                'searchengine'	=> $searchengine,
                'keywords'	=> $keywords
        );
    }
}
?>