<?php

add_shortcode( 'lhg_hplip', 'lhg_hplip_shortcode');
add_shortcode( 'lhg_drive_intro', 'lhg_drive_intro_shortcode');
add_shortcode( 'lhg_mainboard_intro', 'lhg_mainboard_intro_shortcode');
add_shortcode( 'lhg_mainboard_lspci', 'lhg_mainboard_lspci_shortcode');
add_shortcode( 'lhg_donation_table', 'lhg_donation_table_shortcode');
add_shortcode( 'lhg_donation_list', 'lhg_donation_list_shortcode');
add_shortcode( 'lhg_scancommand', 'lhg_scancommand_shortcode');
add_shortcode( 'lhg_donation_testing', 'lhg_donation_testing');
add_shortcode( 'lhg_scan_overview', 'lhg_scan_overview_shortcode');

function lhg_drive_intro_shortcode($attr) {
        global $lang;
        global $region;

        # Printer name = $printer_name
	$title=translate_title(get_the_title());
	$title_orig=get_the_title();
	$s=explode("(",$title);
	$drive_name=trim($s[0]);

        # Printer type
        $printer_type = "";
        $printer_type_de = " ";
        if ( (strpos($title_orig,"DVD Writer") > 0 ) or (strpos($title_orig,"DVD-Brenner") > 0 )) {
	        $drive_type = "DVD writer";
        	$drive_type_de = "einen DVD-Brenner";
        	$drive_type_fr = "un graveur de DVD";
        	$drive_type_es = "una grabadora de DVD";
        	$drive_type_it = "un masterizzatore DVD";
	}elseif (strpos($title_orig,"DVD") > 0 ) {
	        $drive_type = "DVD drive";
        	$drive_type_de = "ein DVD-Laufwerk";
        	$drive_type_fr = "un lecteur de DVD";
        	$drive_type_es = "una unidad de DVD";
        	$drive_type_it = "un drive DVD";
                $genus_de = "neutral";

	}

        if ( preg_match("/blu.ray/i",$title_orig) == 1 )  {
	        $drive_type = "Blu-Ray drive";
        	$drive_type_de = "ein Blu-Ray Laufwerk";
        	$drive_type_fr = "un graveur de Blu-Ray";
        	$drive_type_es = "una grabadora de Blu-Ray";
        	$drive_type_it = "un masterizzatore Blu-Ray";
                $genus_de = "neutral";
	}


        if ( (strpos($title_orig,"Flash Drive") > 0 ) &&
	     (strpos($title_orig,"USB") > 0 ) ) {
	        $drive_type = "USB flash drive";
        	$drive_type_de = "ein USB-Memorystick";
        	$drive_type_fr = "un lecteur flash USB";
        	$drive_type_es = "una unidad flash USB";
        	$drive_type_it = "un flash drive USB";
		$genus_de = "male";

	}

        if (strpos($title_orig,"SSD,") > 0 ) {
	        $drive_type = "SSD";
        	$drive_type_de = "eine SSD";
        	$drive_type_fr = "un SSD";
        	$drive_type_es = "un SSD";
        	$drive_type_it = "un SSD";
                $genus_de = "female";
	}

        if ( (strpos($title_orig,"Harddisk,") > 0 ) or (strpos($title_orig,"Festplatte,") > 0 ) ) {
	        $drive_type = "harddisk";
        	$drive_type_de = "eine Festplatte";
        	$drive_type_fr = "un disque dur";
        	$drive_type_es = "un disco duro";
        	$drive_type_it = "un disco rigido";
                $genus_de = "female";
	}

        if (preg_match("/([0-9]{1,3} GB|[0-9]{1,3}GB|[0-9]{1,3}TB|[0-9]{1,3} TB|[0-9].[0-9]TB|[0-9].[0-9]?TB)/i",$title_orig,$match) == 1 ) {
                $match = $match[0];
	        $drive_type .= " with $match storage capacity";
        	$drive_type_de .= " mit $match Speicherkapazit&auml;t";
        	$drive_type_fr .= " avec une capacité de stockage de $match";
        	$drive_type_es .= " con capacidad de almacenamiento de $match";
        	$drive_type_it .= " con capacità di memorizzazione di $match";
	}

        if ($genus_de == "female") $ErSieEs = "Sie";
        if ($genus_de == "male") $ErSieEs = "Er";
        if ($genus_de == "neutral") $ErSieEs = "Es";

        $output = "The ".$drive_name." is a ".$drive_type.". It is automatically recognized and fully supported by the Linux kernel:";
        $output_de = "Die ".$drive_name." ist eine ".$drive_type_de.". $ErSieEs wird automatisch vom Linux-Kernel erkannt und vollst&auml;ndig unterst&uuml;zt:";
        $output_fr = "Le ".$drive_name." est ".$drive_type_fr.". Il est reconnaît automatiquement et entièrement pris en charge par le noyau Linux:";
        $output_es = "La ".$drive_name." es ".$drive_type_es.". Es reconocida automáticamente y totalmente soportado por el núcleo Linux";
        $output_it = "La ".$drive_name." è ".$drive_type_it.". E 'riconosce automaticamente e pienamente supportato dal kernel Linux: ";

	if ($lang == "de") $output = $output_de;
	if ($region == "fr") $output = $output_fr;
	if ($region == "es") $output = $output_es;
	if ($region == "it") $output = $output_it;

        return $output;
}


function lhg_mainboard_intro_shortcode($attr) {
        global $lang;
        global $region;

        # Printer name = $printer_name
	$title=translate_title(get_the_title());
	$title_orig=get_the_title();
	$s=explode("(",$title);
	$mainboard_name=trim($s[0]);
	$mainboard_properties=trim($s[1]);

        # Mainboard type
        if ( strpos($mainboard_properties,"Laptop") > 0 ) $type = "laptop";
        if ( strpos($mainboard_properties,"Desktop") > 0 ) $type = "desktop PC";
        if ( $type != "" ) $typetext = "is a $type and ";

        $output = "<p>The ".$mainboard_name.' '. $typetext.' was successfully tested in configuration</p>
<pre class="brush: plain; title: dmesg | grep DMI; notranslate" title="dmesg | grep DMI">
'.$attr['dmi_output'].'
</pre>
<p>
under '.trim($attr['distribution']).' with Linux kernel version '.trim($attr['version']).'
</p>
';
        return $output;
}

function lhg_mainboard_lspci_shortcode($attr, $content) {
        global $lang;
        global $region;

        # Printer name = $printer_name
	$title=translate_title(get_the_title());
	$title_orig=get_the_title();
	$s=explode("(",$title);
	$mainboard_name=trim($s[0]);
	$mainboard_properties=trim($s[1]);

        $lspci_lines = explode("\n",$content);

        $output =

'<h3>Hardware Overview</h3>
<p>
The following hardware components are part of the '.$mainboard_name.' and are supported by the listed kernel drivers:
</p>
<pre class="brush: plain; title: lspci -nnk; notranslate" title="lspci -nnk">';

#Strange things happen with out lspci output. Somehow, newlines are replaced by <br> if text is transferred as $content
foreach ($lspci_lines as $line) {
        $output .= str_replace("<br />","\n",$line);
}

$output .= '</pre>';
        return $output;
}


function lhg_hplip_shortcode($attr) {
        global $lang;
        global $region;

        # Printer name = $printer_name
	$title=translate_title(get_the_title());
	$s=explode("(",$title);
	$printer_name=trim($s[0]);

        # Printer type
        $printer_type = "";
        $printer_type_de = " ";
        if (strpos($title,"aser") > 0 ) $printer_type = "laser";
        if (strpos($title,"nkjet") > 0 ) $printer_type = "inkjet";
        if (strpos($title,"inten") > 0 ) $printer_type_de = "Tintenstrahl-";
        if (strpos($title,"encre") > 0 ) $printer_type_fr = "imprimante &agrave; jet d'encre";
        if (strpos($title,"Tinta") > 0 ) $printer_type_es = "impresora de inyección de tinta";
        if (strpos($title,"tampante") > 0 ) $printer_type_it = "inkjet stampante";


        # With scanner?
        $scanner_available = 0;
        if (strpos($title,"canner") > 0 ) $scanner_available = 1;
        if (strpos($title,"canneur") > 0 ) $scanner_available = 1;
        if (strpos($title,"Esc") > 0 ) $scanner_available = 1;

        #print "T: $title<br> - SA: $scanner_available <br>";

	$output = 'The '.$printer_name.' is an ';
        $output_de = 'Der '.$printer_name.' ist ein ';
        $output_fr = 'La '.$printer_name.' est une ';
        $output_es = 'El '.$printer_name.' es ';
        $output_it = 'La '.$printer_name.' è ';


        if ($scanner_available == 0) $output .= $printer_type.' printer.';
        if ($scanner_available == 0) $output_de .= $printer_type_de.'Drucker.';
        if ($scanner_available == 0) $output_fr .= $printer_type_fr;
        if ($scanner_available == 0) $output_es .= "una ".$printer_type_fr;
        if ($scanner_available == 0) $output_it .= "una ".$printer_type_it;

        if ($scanner_available > 0)  $output .= 'all-in-one device with '.$printer_type.' printer and scanner. ';
        if ($scanner_available > 0)  $output_de .= 'Multifunktionsdruckert mit '.$printer_type.'Drucker und Scanner. ';
        if ($scanner_available > 0)  $output_fr .= 'tout-en-un '.$printer_type_fr.' avec une scanner. ';
        if ($scanner_available > 0)  $output_es .= 'un dispositivo todo-en-uno con la '.$printer_type_es.'. ';
        if ($scanner_available > 0)  $output_it .= 'una multifunction '.$printer_type_es.'. ';

        if (isset($attr['usb'])) $output .= 'The device is connected via USB and has the USB ID
<pre class="brush: plain; title: lsusb; notranslate" title="lsusb">
'.$attr['usb'].'
</pre>
';

        if (isset($attr['usb'])) $output_de .= 'Das Ger�t kann per USB angeschlossen werden und besitzt die USB ID
<pre class="brush: plain; title: lsusb; notranslate" title="lsusb">
'.$attr['usb'].'
</pre>
';

        if (isset($attr['usb'])) $output_fr .= 'L\'appareil est connecté via USB et possède l\'ID USB
<pre class="brush: plain; title: lsusb; notranslate" title="lsusb">
'.$attr['usb'].'
</pre>
';

        if (isset($attr['usb'])) $output_es .= 'El dispositivo está conectado a través de USB y cuenta con el ID USB
<pre class="brush: plain; title: lsusb; notranslate" title="lsusb">
'.$attr['usb'].'
</pre>
';

        if (isset($attr['usb'])) $output_it .= 'Il dispositivo è collegato via USB e ha la ID USB
<pre class="brush: plain; title: lsusb; notranslate" title="lsusb">
'.$attr['usb'].'
</pre>
';


        $output .= ' The '.$printer_name.' is fully supported under Linux thanks to the
<a href="'.$attr['url'].'">HPLIP drivers provided by HP</a>. At least the version '.$attr['version'].' of the  HPLIP
drivers is necessary in order to fully support the '.$printer_name.'.
Under Ubuntu the needed drivers can be installed via the apt-get package manager:
<pre class="brush: plain; notranslate" >
sudo apt-get install hplip hpijs hplip-gui
</pre>
Otherwise, the drivers can be downloaded from <a href="http://hplipopensource.com/hplip-web/downloads.html">HPLIP web site</a>. The downloaded files (eventually after unpacking them) can be installed (as root):
<pre class="brush: plain; notranslate" >
sh hplip-'.$attr['version'].'.run
</pre>
Under Ubuntu this step is not necessary (because installation is done by the package management system) and one can immediately skip to the configuration step:
<pre class="brush: plain; notranslate" >
sudo hp-setup
</pre>
';

        $output_de .= ' Der '.$printer_name.' wird vollst&auml;ndig unter Linux unterst&uuml;tzt dank der
<a href="'.$attr['url'].'">HPLIP Treiber</a> welche von HP zur Verf&uuml;gung gestellt werden. Es wird mindestens die Version '.$attr['version'].' des HPLIP
Treibers ben&ouml;tigt, um den '.$printer_name.' anzusprechen.
Unter Ubuntu kann der Treiber mittels Paket-Manager und apt-get installiert werden:
<pre class="brush: plain; notranslate" >
sudo apt-get install hplip hpijs hplip-gui
</pre>
Bei anderen Linux-Distributionen kann der Treiber auch von der <a href="http://hplipopensource.com/hplip-web/downloads.html">HPLIP Webseite</a> heruntergeladen werden. Die heruntergeladenen
Dateien k&ouml;nnen (nach dem Entpacken) als Benuter "root" installiert werden mittels:
<pre class="brush: plain; notranslate" >
sh hplip-'.$attr['version'].'.run
</pre>
Die Konfiguration des Treibers erfolgt mittels des Programms hp-setup:
<pre class="brush: plain; notranslate" >
sudo hp-setup
</pre>
';

$output_fr .= 'La '.$printer_name.' est entièrement pris en charge sous Linux grâce aux pilotes
<a href="'.$attr['url'].'">pilotes HPLIP</a> fournies par HP. Au moins la version '.$attr['version'].' des pilotes hplip
est nécessaire afin de soutenir pleinement la '.$printer_name.'.
Sous Ubuntu les pilotes nécessaires peuvent être installés via apt-get gestionnaire de paquets:
<pre class="brush: plain; notranslate" >
sudo apt-get install hplip hpijs hplip-gui
</pre>
Sinon, les pilotes peuvent être téléchargés à partir
<a href="http://hplipopensource.com/hplip-web/downloads.html">HPLIP site web</a>. Les fichiers
téléchargés (éventuellement après déballage) peuvent être installés (en tant que root):
<pre class="brush: plain; notranslate" >
sh hplip-'.$attr['version'].'.run
</pre>
Sous Ubuntu cette étape est pas nécessaire (car l\'installation se fait par le système de gestion des paquets) et on peut immédiatement
passer à l\'étape de configuration:
<pre class="brush: plain; notranslate" >
sudo hp-setup
</pre>
';

$output_es .= 'La '.$printer_name.' es totalmente compatible con Linux gracias a los
<a href="'.$attr['url'].'">controladores HPLIP</a> proporcionadas por HP.
Por lo menos la versión '.$attr['version'].'
de los conductores HPLIP es necesario con el fin de apoyar plenamente la '.$printer_name.'.
Bajo Ubuntu los controladores necesarios se pueden instalar a través de apt-get gestor de paquetes:
<pre class="brush: plain; notranslate" >
sudo apt-get install hplip hpijs hplip-gui
</pre>
De lo contrario, los controladores se pueden descargar desde el
<a href="http://hplipopensource.com/hplip-web/downloads.html">sitio web HPLIP</a>. Los archivos descargados (finalmente
después de desempaquetar ellos) se pueden instalar (como root):
<pre class="brush: plain; notranslate" >
sh hplip-'.$attr['version'].'.run
</pre>
Bajo Ubuntu este paso no es necesario (ya que la instalación se realiza por el sistema de gestión de paquetes) y
uno puede saltar inmediatamente al paso de configuración:
<pre class="brush: plain; notranslate" >
sudo hp-setup
</pre>
';

$output_it .= 'La '.$printer_name.' è pienamente supportato in Linux grazie ai
<a href="'.$attr['url'].'">driver HPLIP</a> forniti da HP.
Almeno la versione '.$attr['version'].' dei driver HPLIP è necessaria al fine di sostenere pienamente la
 '.$printer_name.'.
Sotto Ubuntu i driver necessari possono essere installati tramite apt-get gestore di pacchetti:
<pre class="brush: plain; notranslate" >
sudo apt-get install hplip hpijs hplip-gui
</pre>
In caso contrario, i driver possono essere scaricati dal 
<a href="http://hplipopensource.com/hplip-web/downloads.html">sito web HPLIP</a>. I file scaricati
(eventualmente dopo estrarli) possono essere installate (come root):
<pre class="brush: plain; notranslate" >
sh hplip-'.$attr['version'].'.run
</pre>
Sotto Ubuntu questo passaggio non è necessario (perché l\'installazione viene eseguita dal sistema
di gestione dei pacchetti) e si può passare immediatamente alla fase di configurazione:
<pre class="brush: plain; notranslate" >
sudo hp-setup
</pre>
';


if ($scanner_available > 0) $output .= '
<h3>Scanner:</h3>
The scanner can be used together with <a href="http://www.xsane.org/">XSane</a>, which can
be installed under Ubuntu by:
<pre class="brush: plain; notranslate" >
sudo apt-get install xsane
</pre>

If the scanner is found, one can also scan with the console program
<pre class="brush: plain; notranslate" >
hp-scan
</pre>'.'
After placing a sheet of paper on the scan unit and presses the scan button on the printer, a file “hpscan001.png” is created in the home directory.
';


if ($scanner_available > 0) $output_de .= '
<h3>Scanner:</h3>
Der Scanner kann unter Linux z.B. mittels des Programms <a href="http://www.xsane.org/">XSane</a> angesprochen werden, welches unter Ubuntu installiert wird mittels
<pre class="brush: plain; notranslate" >
sudo apt-get install xsane
</pre>

Au&szlig;erdem ist es m&ouml;glich, unter der Konsole einen Scan auszuf&uuml;hren mittels des Programms
<pre class="brush: plain; notranslate" >
hp-scan
</pre>'.'
Nachdem das zu scannende Papier auf der Scanner-Einheit plaziert wurde und der "Scan"-Knopf am Drucker gedr&uuml;ckt wurde, wird die "hpscan001.png"
im Home-Verzeichnis erstellt.
';

if ($scanner_available > 0) $output_fr .= '
<h3>Scanner:</h3>
Le scanner peut être utilisé conjointement ave <a href="http://www.xsane.org/">XSane</a> ce qui peut
être installé sous Ubuntu par:
<pre class="brush: plain; notranslate" >
sudo apt-get install xsane
</pre>

Si le scanner se trouve, on peut également numériser avec le programme de la console
<pre class="brush: plain; notranslate" >
hp-scan
</pre>'.'

Après avoir placé une feuille de papier sur l\'unité de balayage et appuie sur le bouton de numérisation sur l\'imprimante, un fichier "hpscan001.png"
est créé dans le répertoire de la maison.
';


if ($scanner_available > 0) $output_es .= '
<h3> Escáner: </h3>
El escáner se puede utilizar junto con <a href="http://www.xsane.org/"> XSane </a>, lo que puede
se instalará bajo Ubuntu por:
<pre class="brush: plain; notranslate">
sudo apt-get install xsane
</pre>

Si se encuentra el escáner, también se puede escanear con el programa de consola
<pre class = "brush: plain; notranslate">
hp-scan
</pre> '.'
Después de colocar una hoja de papel en la unidad de exploración y presiona el botón de escaneo de la impresora, un "hpscan001.png"
archivo se crea en el directorio principal.
';

if ($scanner_available > 0) $output_it .= '
<h3> Scanner: </h3>
Lo scanner può essere utilizzato insieme a <a href="http://www.xsane.org/"> XSane </a>, che può
essere installati con Ubuntu da:
<pre class = "brush: plain; notranslate">
sudo apt-get install xsane
</pre>

Se viene trovato lo scanner, si può anche eseguire la scansione con il programma di console
<pre class = "brush: plain; notranslate">
hp-scan
</pre> '.'
Dopo aver posizionato un foglio di carta sulla unità di scansione e preme il pulsante di scansione sulla stampante, un file "hpscan001.png" viene creata nella directory home.
';

        #$output = do_shortcode($output, false);

if ($lang == "de") $output = $output_de;
if ($region == "fr") $output = $output_fr;
if ($region == "es") $output = $output_es;
if ($region == "it") $output = $output_it;


if (!isset($attr['url'])) $output = '<h1>ERROR: URL missing</h1><br>'. $output;

        return $output;
}


#
#
##### Show latest scan results
#
#


function lhg_scan_overview_shortcode($attr) {

	require_once(plugin_dir_path(__FILE__)."../../lhg-hardware-profile-manager/templates/uid.php");
	require_once(plugin_dir_path(__FILE__)."lhg.conf");

	$myquery = $lhg_price_db->prepare("SELECT id, sid, pub_id, scandate, kversion, distribution, status FROM `lhgscansessions` GROUP BY scandate ORDER BY scandate DESC LIMIT 10;");
	#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
	$identified_scans = $lhg_price_db->get_results($myquery);


        #$output = "List of Scans";

	$output .= "<h2>Latest Hardware Scans:</h2>";
        $output .=  '<table id="registration">';
        $output .= '<tr id="header">

                <td id="title-scandate">Scan Date</td>
                <td id="title-scanstatus">Status</td>
                ';

        $output .= '<td id="title-scanlist-distribution" width="30%">Distribution</td>
                      
                      <td id="hwscan-col2" width="20%">Kernel Version</td>
                <td id="hwscan-col2" width="13%">Hardware Components</td>
                </tr>';

        foreach($identified_scans as $a_identified_scan){

                        $SID = $a_identified_scan->sid;
                        $pub_id = $a_identified_scan->pub_id;

		        if ($pub_id == "") {
        		        $pub_id = lhg_create_pub_id($SID);
			}


			$myquery = $lhg_price_db->prepare("SELECT COUNT(DISTINCT idstring) FROM `lhghwscans` WHERE sid = %s AND postid = 0", $SID);
			#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
			$num_unidentified_hw = $lhg_price_db->get_var($myquery);
                        #print "NU: $num_unidentified_hw";

			$myquery = $lhg_price_db->prepare("SELECT COUNT(DISTINCT postid) FROM `lhghwscans` WHERE sid = %s AND postid <> 0", $SID);
			#$sql = "SELECT id FROM `lhgshops` WHERE region <> \"de\"";
			$num_identified_hw = $lhg_price_db->get_var($myquery);
                        #print "NI: $num_identified_hw";

                        $scandate = $a_identified_scan->scandate;
			$scandate_txt = gmdate("Y-m-d, H:i:s", $scandate);

                        $distribution = "unknown";
                        $kversion = "unkwnown";

                        $distribution = $a_identified_scan->distribution;
                        $kversion = $a_identified_scan->kversion;
                        $scan_status_raw = $a_identified_scan->status;

                        #unassigned scan
                        #print "SST:
                        if ($scan_status_raw == "") $scan_status_raw = "new";
                        if ($scan_status_raw == "duplicate") $scan_status_raw = "new";
                        # ToDo: Duplicates should be handled separately with pointing system not taking scans into account

                        if ($scan_status_raw == "new")     $scan_status = '<span class="scan-status-new">New</span>';
                        if ($scan_status_raw == "ongoing") $scan_status = '<span class="scan-status-ongoing">Ongoing</span>';
                        if ($scan_status_raw == "complete") $scan_status = '<span class="scan-status-done">Done</span>';
                        if ($scan_status_raw == "feedback") $scan_status = '<span class="scan-status-ongoing">User feedback needed</span>';

                        $logo = get_distri_logo($distribution);


			$output .= "<tr id=\"regcont\">";


                        $output .= "
                        <td id=\"col-hw\">

                        ".'<div class="subscribe-hwtext-scanlist"><div class="subscribe-hwtext-span-scanlist">&nbsp;<a href="/hardware-profile/system-'.$pub_id.'" target="_blank">'.$scandate_txt.' (see details ...)</a></div></div>';
			$output .= " </td>";

                        # Status ---
                        $output .= "
                        <td id=\"col-scan-status\">

                        ".'<div class="subscribe-hwtext-scanlist"><div class="subscribe-hwtext-span-scanstatus">'.$scan_status.'</div></div>';
			$output .= " </td>";


                        $output .= "
                        <td id=\"col-scanresults-distri\">
                        ".'<div class="scan-overview-distri-logo"><img src="'.$logo.'" width="40" ></div>'.

                        "<div class='subscribe-column-2-pub'>$distribution</div>
                        </td>";

                        $output .= "
                        <td id=\"col4\">
                        <span class='subscribe-column-2'>$kversion</span>
                        </td>";


                        #$registration_date=$a_subscription->dt;
                        #list ($registration_date, $registration_time) = explode(" ",$registration_date);
                        $categorypart2 = "";
                        if ($category_name2 != "")  $categorypart2 = "<br>($category_name2)";

                        $output .= "
                        <td id=\"col2\">
                        <span class='subscribe-column-1'><center>Identified: $num_identified_hw <br> Unknown: $num_unidentified_hw  </center></span>
                        </td>";



                        $output .= "</tr>\n";


        }

        #var_dump($identified_scans);

        $output .= "</table>";
        return $output;
}

function lhg_donation_table_shortcode($attr) {

	global $region;
        global $lang;
	global $top_users;
        global $donation;
        global $txt_username;
        global $txt_cp_title;
	global $txt_cp_karma;
	global $txt_cp_points;
	global $txt_cp_donates_to;
	global $txt_cp_longtext;
	global $txt_cp_language;
        global $txt_cp_quarterly;
        global $txt_cp_totalkarma;
        global $txt_cp_details;

        # before we create the table we update the data in the transverse DB
        lhg_update_karma_values('quarterly');


        # How many users to show for ongoing quarter
        $max_users_to_show = 10;

        $langurl = lhg_get_lang_url_from_region( $region );

	# Show table of top users of ongoing Quarter
	list($list_guid, $list_points_guid) = cp_getAllQuarterlyPoints_transverse();


        $i = 0;

        if (sizeof($list_guid) > 0) $output .= '<table id="quarterly-points-table">
                <tr id="quarterly-points-header-row">
                  <td class="qrtly-1" id="quarterly-points-1">'.$txt_cp_quarterly.'</td>
                  <td class="qrtly-2" id="quarterly-points-2"></td>
                  <td class="qrtly-3" id="quarterly-points-3">'.$txt_username.'</td>
                  <td class="qrtly-4" id="quarterly-points-3">'.$txt_cp_details.'</td>
                  <td class="qrtly-5" id="quarterly-points-3">'.$txt_cp_totalkarma.'</td>
                </tr>
                ';

                if (sizeof($list_guid) > 0)
		foreach($list_guid as $guid){
                        # skip deleted users
                        # skip anonymously added posts, i.e. user = user-submitted-posts
	                $user_tmp = lhg_get_userdata_guid($guid);
                        $user=$user_tmp[0];

                        if ( $user !== false )
                        if ($uid != 12378){
                                #var_dump($user);
                                #print sizeof($uid)."<p>";
        	                $user_nicename = $user->user_nicename;
                	        $points = $list_points_guid[$i];

				# get user's avatar
                                $avatar = $user->avatar;
                                # repair URL if linking to .de avatar on .com server
                                if (strpos($avatar,"src='/avatars/") > 0) $avatar = str_replace( "src='/avatars/" , "src='http://www.linux-hardware-guide.de/avatars/" , $avatar );

                                $wpuid_de = $user->wpuid_de;
                                $wpuid_com = $user->wpuid;
	                        $user_language_txt = $user->language;
        			$user_language_flag= lhg_show_flag_by_lang ( $user_language_txt );
			        $total_karma = $user->karma_com + $user->karma_de; //$num_com * 3 + $num_art * 50;

                                if ($lang == "de") $uid = $user->wpuid_de;
                                if ($lang == "com") $uid = $user->wpuid;

                        //registration date
                        #$regdate = date("d. M Y", strtotime(get_userdata( $uid ) -> user_registered ) );

                        //donates to
                        if ($user->donation_target_date_de > $user->donation_target_date_com) $donation_target = $user->donation_target_de;
                        if ($user->donation_target_date_de <= $user->donation_target_date_com) $donation_target = $user->donation_target_com;
                        if ($donation_target == "") $donation_target = 1;
                        if ($donation_target == 0) $donation_target = 1;

                        //print_r($y);
                        //if ($langurl != "") $langurl = "/".$langurl;



	                #print "Name: ".$user->user_nicename." ($uid) - $points<br>";

			$output .= '<tr>

<td class="qrtly-1" >
	    <div class="userlist-place-quarter">'.
        	    ($points).' '.$txt_cp_points.'
    	    </div>
</td>


<td class="quartery-points-avatar qrtly-2">';

# TODO: localized hardware profile should be linked. Not US version

# linked avatar to user page if on local server
# link avatar to guser page, if user present on other servers
if ($lang == "de") {
	if ($user->wpuid_de != 0) {
		$output .= '<a href="/hardware-profile/user'.$user->wpuid_de.'" class="recent-comments">';
                $close0 = 1; # remember that link has to be closed
        } else {
		$output .= '<a href="/hardware-profile/guser'.$guid.'" class="recent-comments">';
                $close0 = 1;
        }
}

if ($lang != "de") {
	if ($user->wpuid != 0) {
		$output .= '<a href="/hardware-profile/user'.$user->wpuid.'" class="recent-comments">';
                $close0 = 1; # remember that link has to be closed
        } else {
		$output .= '<a href="/hardware-profile/guser'.$guid.'" class="recent-comments">';
                $close0 = 1; # remember that link has to be closed
        }
}

$output .='    <div class="userlist-avatar">'.
      $avatar.'
    </div> ';

if ($close0 == 1) $output .= '</a>';

$output .= '</td>


<td class="qrtly-3">
          <div class="userlist-displayname">';

# show link to user page if on local server
# link to guser page, if user present on other servers
if ($lang == "de") {
	if ($user->wpuid_de != 0) {
        	$output .= '		<a href="/hardware-profile/user'.$user->wpuid_de.'" class="recent-comments">';
                $close1 = 1; # remember that link has to be closed
        } else {
        	$output .= '		<a href="/hardware-profile/guser'.$guid.'" class="recent-comments">';
                $close1 = 1;
        }
}

if ($lang != "de") {
	if ($user->wpuid != 0) {
        	$output .= '		<a href="/hardware-profile/user'.$user->wpuid.'" class="recent-comments">';
                $close1 = 1;
        } else {
        	$output .= '		<a href="/hardware-profile/guser'.$guid.'" class="recent-comments">';
                $close1 = 1;
        } 
}

$output .= $user_nicename;

if ($close1 == 1) $output .= '</a>';

$output .='
          </div>
</td>


<td class="qrtly-4">
    <div class="quarterly-points-userlist-details">
      '.$txt_cp_donates_to.': '.$donation[$donation_target]["Name"].'<br>
      '.$txt_cp_language.': ';

#check if this user is present on "de" server
if ($user->wpuid_de != 0) $output .= lhg_show_flag_by_lang( "de" )." ";
if ($user->wpuid != 0) $output .= $user_language_flag;

      $output .=
      '
    </div>
</td>

<td class="qrtly-5">
          <div class="quartly-points-totalpoints">
	     '.$total_karma.' '.$txt_cp_points.'<br>
          </div>
</td>


</tr>';

		}

                        $i++;
                        if ($i > $max_users_to_show) break;
		}

                if (sizeof($list_guid) > 0) $output .= "</table>";

        return $output;

}

function lhg_donation_table_shortcode_singe_language($attr) {

	global $region;
	global $top_users;
        global $donation;
        global $txt_cp_title;
	global $txt_cp_karma;
	global $txt_cp_points;
	global $txt_cp_donates_to;
	global $txt_cp_longtext;
	global $txt_cp_language;


        # before we create the table we update the data in the transverse DB
        lhg_update_karma_values('quarterly');


        # How many users to show for ongoing quarter
        $max_users_to_show = 10;

        $langurl = lhg_get_lang_url_from_region( $region );

		#extract($args, EXTR_SKIP);
		#echo $before_widget;
		//$title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);
                #$output .='<i class="icon-trophy menucolor"></i>&nbsp;'.$txt_cp_title;
		#if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };

		//set default values
		#if($instance['num'] == '' || $instance['num'] == 0) { $instance['num'] = 1; }
		#if($instance['text'] == '') { $instance['text'] = '%user% (%points%)';}


		# Show table of top users of ongoing Quarter
		list($list_uid, $list_points) = cp_getAllQuarterlyPoints();
		#list($list_guid, $list_points_guid) = cp_getAllQuarterlyPoints_transverse();


                #print "<br>Users:";
                #var_dump($list_uid);
                $i = 0;

                if (sizeof($list_uid) > 0) $output .= '<table id="quarterly-points-table">
                <tr id="quarterly-points-header-row">
                  <td class="qrtly-1" id="quarterly-points-1">Quarterly Points</td>
                  <td class="qrtly-2" id="quarterly-points-2"></td>
                  <td class="qrtly-3" id="quarterly-points-3">Username</td>
                  <td class="qrtly-4" id="quarterly-points-3">Details</td>
                  <td class="qrtly-5" id="quarterly-points-3">Total Karma</td>
                </tr>
                ';

                if (sizeof($list_uid) > 0)
		foreach($list_uid as $uid){
                        # skip deleted users
                        # skip anonymously added posts, i.e. user = user-submitted-posts
	                $user = get_userdata($uid);

                        if ( $user !== false )
                        if ($uid != 12378){
                                #var_dump($user);
                                #print sizeof($uid)."<p>";
        	                $name = $user->first_name." ".$user->last_name;
                	        $points = $list_points[$i];
                        	$avatar = get_avatar($uid, 40);
	                        $user_language_txt = lhg_get_locale_from_id ( $uid );
        			$user_language_flag= lhg_show_flag_by_lang ( $user_language_txt );
			        $total_karma = cp_getPoints( $uid ); //$num_com * 3 + $num_art * 50;

                        	//registration date
                        $regdate = date("d. M Y", strtotime(get_userdata( $uid ) -> user_registered ) );

                        //donates to
			$donation_target = get_user_meta($uid,'user_donation_target',true);
                        if ($donation_target == "") $donation_target = 1;

                        //print_r($y);
                        //if ($langurl != "") $langurl = "/".$langurl;



	                #print "Name: ".$user->user_nicename." ($uid) - $points<br>";

			$output .= '<tr>

<td class="qrtly-1" >
	    <div class="userlist-place-quarter">'.
        	    ($points).' '.$txt_cp_points.'
    	    </div>
</td>


<td class="quartery-points-avatar qrtly-2">
<a href="./hardware-profile/user'.$uid.'" class="recent-comments">
    <div class="userlist-avatar">'.
      $avatar.'
    </div>
</a>
</td>


<td class="qrtly-3">
          <div class="userlist-displayname">
		<a href="./hardware-profile/user'.$uid.'" class="recent-comments">
	            	'.$user->nickname.'
                </a>

          </div>
</td>


<td class="qrtly-4">
    <div class="quarterly-points-userlist-details">
      '.$txt_cp_donates_to.': '.$donation[$donation_target]["Name"].'<br>
      '.$txt_cp_language.': '.$user_language_flag.'
    </div>
</td>

<td class="qrtly-5">
          <div class="quartly-points-totalpoints">
	     '.$total_karma.' '.$txt_cp_points.'<br>
          </div>
</td>


</tr>';

		}

                        $i++;
                        if ($i > $max_users_to_show) break;
		}

                if (sizeof($list_uid) > 0) $output .= "</table>";

        return $output;

}

function lhg_donation_list_shortcode($attr) {
        global $donation;

        $output ="<ul>";
        foreach ($donation as $key => $item){

                $output .= "<li>".$item["Name"]."</li>";
                #$donation_targets[$j] = $donation[$key]["Name"];
                #$donation_points[$j] = $points;
                #$j++;

	}
        $output .= "</ul>";

        return $output;
}

function lhg_scancommand_shortcode($attr) {
        global $lang;

        $output ="perl <(wget -q http://linux-hardware-guide.com/scan-hardware -O -) ";
        $uid = get_current_user_id();
        if ( ($lang == "de") && ($uid != 0) ) $output .= "-d".$uid;
        if ( ($lang != "de") && ($uid != 0) ) $output .= "-u".$uid;

        return $output;
}


# This function is needed temporarily only.
# It allows creating contents visible only to users participating in our beta testing 
function lhg_donation_testing($attr, $content) {
        global $lang;
        $uid = get_current_user_id();
        $guid = lhg_get_guid( $uid );

        # user accounts are currently hard coded. Will be removed after beta testing
        if (
        	( ($lang != "de") and ($uid == 24294)) or   # testuer1 .com
        	  ($guid == 1)  or                          # cptpike .com & .de
        	  ($uid == 1)                               # admin .com & .de
           ) {                           
                return do_shortcode( $content );
	}

        return;
}

