<?php

add_shortcode( 'lhg_hplip', 'lhg_hplip_shortcode');
add_shortcode( 'lhg_drive_intro', 'lhg_drive_intro_shortcode');

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
	}

        if ( (strpos($title_orig,"Flash Drive") > 0 ) &&
	     (strpos($title_orig,"USB") > 0 ) ) {
	        $drive_type = "USB flash drive";
        	$drive_type_de = "ein USB-Memorystick";
        	$drive_type_fr = "un lecteur flash USB";
        	$drive_type_es = "una unidad flash USB";
        	$drive_type_it = "un flash drive USB";
	}

        if (strpos($title_orig,"SSD,") > 0 ) {
	        $drive_type = "SSD";
        	$drive_type_de = "eine SSD";
        	$drive_type_fr = "un SSD";
        	$drive_type_es = "un SSD";
        	$drive_type_it = "un SSD";
	}

        if ( (strpos($title_orig,"Harddisk,") > 0 ) or (strpos($title_orig,"Festplatte,") > 0 ) ) {
	        $drive_type = "harddisk";
        	$drive_type_de = "eine Festplatte";
        	$drive_type_fr = "un disque dur";
        	$drive_type_es = "un disco duro";
        	$drive_type_it = "un disco rigido";
	}

        if (preg_match("/([0-9]{1,3} GB|[0-9]{1,3}GB|[0-9]{1,3}TB|[0-9]{1,3} TB|[0-9].[0-9]TB|[0-9].[0-9]?TB)/i",$title_orig,$match) == 1 ) {
                $match = $match[0];
	        $drive_type .= " with $match storage capacity";
        	$drive_type_de = " mit $match Speicherkapazit&auml;t";
        	$drive_type_fr = " avec une capacité de stockage de $match";
        	$drive_type_es = " con capacidad de almacenamiento de $match";
        	$drive_type_it = " con capacità di memorizzazione di $match";
	}

        $output = "The ".$drive_name." is a ".$drive_type.". It is automatically recognized and fully supported by the Linux kernel:";
        $output_de = "Die ".$drive_name." ist eine ".$drive_type_de.". Er wird automatisch vom Linux-Kernel erkannt und vollst&auml;ndig unterst&uuml;zt:";
        $output_fr = "Le ".$drive_name." est ".$drive_type_fr.". Il est reconnaît automatiquement et entièrement pris en charge par le noyau Linux:";
        $output_es = "La ".$drive_name." es ".$drive_type_es.". Es reconocida automáticamente y totalmente soportado por el núcleo Linux";
        $output_it = "La ".$drive_name." è ".$drive_type_it.". E 'riconosce automaticamente e pienamente supportato dal kernel Linux: ";

	if ($lang == "de") $output = $output_de;
	if ($region == "fr") $output = $output_fr;
	if ($region == "es") $output = $output_es;
	if ($region == "it") $output = $output_it;

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

add_shortcode( 'lhg_scan_overview', 'lhg_scan_overview_shortcode');

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

		        if (pub_id == "") {
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
