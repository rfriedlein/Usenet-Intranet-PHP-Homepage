<?php include('intranet/serverconfig.php'); ?>
<?php include('intranet/lib/functions.php'); ?>

<?php if($config['uTorrent']) {include('intranet/lib/utorrent_php_api.php');} ?>
<?php if($config['transmission']) {include('intranet/lib/transmissionrpc.class.php');} ?>
<!doctype html>
<html>
	<head>
		<title><?= $config['wifiName']; ?> Intranet</title>
		<link rel="stylesheet" href="intranet/style.css" />
		<link rel="shortcut icon" href="favicon.ico" />
                <script src="intranet/js/jquery.js"></script>
                <script src="intranet/js/scripts.js"></script>

	</head>
	<body>

		<h1><?= $config['wifiName']; ?> Server</h1>

		<?php ## Check if everything is disabled
			if (!$config['sickbeard'] && !$config['couchpotato'] && !$config['headphones'] && !$config['sabnzbd'] && !$config['showWifi'] && !$config['showTrailers']) :

				echo "<img src='intranet/images/mymanjackie.png' />";

			else :
		?>
		
		<?php if( $config['sickbeard'] ) : 
			  if ( $config['sickMissed'] ) : $sbType = "missed"; else: $sbType = "today"; endif;
		?>
		<div class="sickbeardShows">
			<h3>TV Today</h3>
			<?php

				$sbJSONURL = $sickbeardURL."/api/".$config['sickbeardAPI']."/?cmd=future&sort=date&type=".$sbType;

				if($config['debug']) {echo "TV Today URL: ".$sbJSONURL;}

				$sbJSON = file_get_contents($sbJSONURL);
				$sbShows = json_decode($sbJSON);

				echo "<ul class='comingShows'>";

				# List shows
				if(empty($sbShows)){
					# quick check if there are any shows today.
					echo "<li>No shows today.</li>";
				} else {
					# Run through each show
					foreach($sbShows->{'data'}->{$sbType} as $episode) {
						echo "<li>";

						# Sickbeard Popups
						if($config ['sickPopups']) :
						echo "<span class='showPopup'>";
						echo "<img src='".$sickbeardURL."/showPoster/?show=".$episode->{'tvdbid'}."&which=poster' class='showposter' />";
						echo "</span>";
						endif;

						# Show name and number
						echo "<strong class='showname'>".$episode->{'show_name'}."</strong><br />";
						echo "<span class='showep'>".$episode->{'season'}."x".$episode->{'episode'}." - ". $episode->{'ep_name'};
						echo "</li>";
					} 
				}
				echo "</ul>";

				$sbJSONdoneURL = $sickbeardURL."/api/".$config['sickbeardAPI']."/?cmd=history&limit=50";
				$sbJSONdone = file_get_contents($sbJSONdoneURL);
				$sbShowsdone = json_decode($sbJSONdone);
				$todaysDate = date('Y-m-d');

				if($config['debug']){echo "TV Complete Today URL: ".$sbJSONdoneURL;}

				echo "<ul class='snatchedShows'>";

				# List shows
				# Run through each show
				foreach($sbShowsdone->{'data'} as $episode) {

					if (substr($episode->date,0,10) == $todaysDate && $episode->status == "Snatched") :

						// Check Quality Snatched
						if ($episode->{quality} == "SD TV") :
							$quality = "sd";
						elseif ($episode->{quality} == "HD TV") :
							$quality = "hd";
						endif;

						echo "<li class=".$quality.">";

						# Sickbeard Popups
						if($config ['sickPopups']) :
						echo "<span class='showPopup'>";
						echo "<img src='".$sickbeardURL."/showPoster/?show=".$episode->{'tvdbid'}."&which=poster' class='showposter' />";
						echo "</span>";
						endif;

						# Show name and number
						echo "<strong class='showname'>".$episode->{'show_name'}." <small>".$episode->{'season'}."x".$episode->{'episode'}."</small></strong>";
						echo "</li>";

					endif;

				} 
				echo "</ul>";

			?>
		</div>
		<?php endif; ?>

                <?php if( $config['sonarr'] ) :
                          if ( $config['sonarrMissed'] ) : $sbType = "missed"; else: $srType = "today"; endif;
                ?>
                <div class="sonarrShows">
                        <h3>TV Today</h3>
                        <?php

                                // Get cURL resource
                                $curl = curl_init('http://127.0.0.1:8085/api');
                                // Set some options - we are passing in a useragent too here
				    curl_setopt($curl, CURLOPT_HTTPHEADER, ['X-Api-Key' => '$my_key']);
				    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                                // Send the request & save response to $resp

 				//$results = curl_exec($curl);
 				//echo $results;

				$srJSONURL = curl_exec($curl);

				if (curl_errno($curl)) {
        				print "Error: " . curl_error($curl);
    				}

				//var_dump($srJSONURL);
                                if($config['debug']) {echo "TV Today URL: ".$srJSONURL;}

                                $srJSON = file_get_contents($srJSONURL);
   				print_r($srJSON);
                                $srShows = json_decode($srJSON);
				print_r($srShows);

                                echo "<ul class='comingShows'>";

                                # List shows
                                if(empty($srShows)){
                                        # quick check if there are any shows today.
                                        echo "<li>No shows today.</li>";
                                } else {
                                        # Run through each show
                                        foreach($srShows->{'data'}->{$srType} as $episode) {
                                                echo "<li>";

                                                # Sonarr Popups
                                                if($config ['sonarrPopups']) :
                                                echo "<span class='showPopup'>";
                                                echo "<img src='".$sonarrdURL."/MediaCover/".$episode->{'seriesId'}."/poster.jpg' class='showposter' />";
                                                echo "</span>";
                                                endif;

                                                # Show name and number
                                                echo "<strong class='showname'>".$episode['series']->{'title'}."</strong><br />";
                                                echo "<strong class='showname'>".$episode['series']->{'title'}." <small>".$episode['episode']->{'title'}."x".$episode['episode']->{'episodeNumber'};
                                                echo "</li>";
                                        }
                                }
                                echo "</ul>";

                                $srJSONdoneURL = $srJSONURL."/history?page=1&pageSize=15&sortKey=date&sortDir=desc&filterKey=eventType&filterValue=1";
                                $srJSONdone = file_get_contents($srJSONdoneURL);
                                $srShowsdone = json_decode($srJSONdone);
                                $todaysDate = date('Y-m-d');

                                if($config['debug']){echo "TV Complete Today URL: ".$srJSONdoneURL;}

                                echo "<ul class='snatchedShows'>";

                                # List shows
                                # Run through each show
                                foreach($srShowsdone->{'data'} as $episode) {

                                        if (substr($episode->date,0,10) == $todaysDate && $episode->status == "grabbed") :

                                                // Check Quality Snatched
                                                if ($episode->{quality} == "SDTV") :
                                                        $quality = "sd";
                                                elseif ($episode->{quality} == "WEBDL-480p") :
                                                        $quality = "sd";
                                                elseif ($episode->{quality} == "DVD") :
                                                        $quality = "sd";
                                                elseif ($episode->{quality} == "HDTV-720p") :
                                                        $quality = "hd";
                                                elseif ($episode->{quality} == "HDTV-1080p") :
                                                        $quality = "hd";
                                                elseif ($episode->{quality} == "Raw-HD") :
                                                        $quality = "hd";
                                                elseif ($episode->{quality} == "WEBDL-720p") :
                                                        $quality = "hd";
                                                elseif ($episode->{quality} == "Bluray-720p") :
                                                        $quality = "hd";
                                                elseif ($episode->{quality} == "WEBDL-1080p") :
                                                        $quality = "hd";
                                                elseif ($episode->{quality} == "Bluray-1080p") :
                                                        $quality = "hd";
                                                endif;

                                                echo "<li class=".$quality.">";

                                                # Sonarr Popups
                                                if($config ['sonarrPopups']) :
                                                echo "<span class='showPopup'>";
                                                echo "<img src='".$sickbeardURL."/MediaCover/".$episode->{'seriesId'}."/poster.jpg' class='showposter' />";
                                                echo "</span>";
                                                endif;

                                                # Show name and number
                                                echo "<strong class='showname'>".$episode['series']->{'title'}." <small>".$episode['episode']->{'title'}."x".$episode['episode']->{'episodeNumber'}."</small></strong>";
                                                echo "</li>";

                                        endif;

                                }
                                echo "</ul>";

                        ?>
                </div>
                <?php endif; ?>

		<?php ## Action Buttons ?>
		<?php if( $config['sickbeard'] ) : ?>
		<a href="<?= $sickbeardURL; ?>" target="_blank" title="SickBeard" class="actionButton big sickbeard"><span>SickBeard</span></a>
                 <?php endif; ?>
                <?php if( $config['sonarr'] ) : ?>
                <a href="<?= $sonarrURL; ?>" title="Sonarr" class="actionButton big sonarr"><span>Sonarr</span></a>
		<?php endif; ?>
		<?php if( $config['couchpotato'] ) : ?>
		<a href="<?= $couchpotatoURL; ?>" title="CouchPoato" class="actionButton big couchpotato"><span>CouchPotato</span></a>
		<?php endif; ?>
		<?php if( $config['headphones'] ) : ?>
		<a href="<?= $headphonesURL; ?>" title="Headphones" class="actionButton big headphones"><span>Headphones</span></a>
		<?php endif; ?>
                <?php if( $config['deluge'] ) : ?>
                <a href="<?= $delugeURL; ?>" title="Deluge" class="actionButton big deluge"><span>Deluge</span></a>
                <?php endif; ?>

		<?php ## SABnzbd ?>
		<?php if( $config['sabnzbd'] ) : ?>
		<a href="<?= $sabURL; ?>" title="SABnzbd" class="actionButton big sabnzb"><span>SABnzbd</span></a>


		<div class="downloadFrame clearfix"><div class="downloadFrameSlide clearfix">
			<div class="downloadPage downloadPageCurrent">
				<h2>Currently Downloading</h2>
				<a href="#" class="go actionButton small">&gt;</a>
				<?php
					libxml_disable_entity_loader(false); //This fixes the Warning: simplexml_load_file(): I/O warning : failed to load external entity "xxxxxx" on line "xxxxxx", error in the latest version of SABNzbd;
					$sabStatusXML = $sabURL."/sabnzbd/api?mode=qstatus&output=xml&apikey=".$config['sabnzbdAPI'];
					if($config['debug']){echo "SABnzbd Status URL: ".$sabStatusXML;}
					$data = simplexml_load_file($sabStatusXML);
					$filename = $data->jobs[0]->job->filename;
					$mbFull = $data->jobs[0]->job->mb;
					$mbLeft = $data->jobs[0]->job->mbleft;
					$mbDone = $mbFull - $mbLeft;

					if($filename) {

						$mbFullNoRound = explode(".",$mbFull);
						$mbPercent = $mbDone / $mbFullNoRound[0] * 100;
						$mbPercentPretty = explode(".",$mbPercent);

						echo "<span class='currentdl'>";
						if ($data->paused == "True") {echo "PAUSED: ";}
						echo $filename."</span>";
						echo "<progress value='".$mbDone."' max='".$mbFull."'></progress>";
						echo "<span class='stats'>".$mbDone."mb / ".$mbFullNoRound[0]."mb (".$mbPercentPretty[0]."%) @ ". $data->speed ."</span>";

					} else {
						
						echo "<em class='currentdl'>No current downloads</em>";

					}
				?>

			</div>
			<div class="downloadPage downloadPageHistory">
				<h2>Recently Finished</h2>
				<a href="#" class="go actionButton small">&lt;</a>
				<?php

					$data = simplexml_load_file($sabURL."/sabnzbd/api?mode=history&start=0&limit=5&output=xml&apikey=".$config['sabnzbdAPI']);
					echo "<ul>";
					foreach($data->slots[0] as $slot) {
						echo "<li>".$slot->category." - ".$slot->nzb_name."</li>";
					}
					echo "</ul>";
				?>
			</div>
			<?php endif; ?>
		</div></div>

		<?php ## uTorrent Web GUI ?>
		<?php if( $config['uTorrent'] ) : ?>
		<section class="clearfix">
			<a href="http://<?= $config['uTorrentURL']; ?>:<?= $config['uTorrentPort']; ?>" title="Deluge" class="actionButton big utorrent"><span>Deluge Web</span></a>

			<div class="downloadFrame">
				<div class="downloadPage downloadPageCurrent">
					<h2>Currently Downloading</h2>
					<?php

				        $utorrent = new uTorrentAPI($config);

				        // Create some variables
				        $torrentAPI = $utorrent->get_torrent_list();
				        $torrents = $torrentAPI['torrents'];
				        $torrentsComplete = array();
				        $torrentsDownloading = array();

				        // Check if any results are returned
				        if(sizeof($torrents)==0) {

				        	echo "<em>No current downloads</em>";

				        } else {

					        // Run through each torrent and insert in to appropriate variables
					        foreach($torrents as $torrent) {
					            if($torrent[4] == "1000") {
					                array_push($torrentsComplete, $torrent);
					            } else {
					                array_push($torrentsDownloading, $torrent);
					            }
					        }

					        // Cut off array at 5 each
					        $torrentsDownloading = array_slice($torrentsDownloading,0,5);

					        // List all pending downloads
					        foreach($torrentsDownloading as $torrentDone) {
					            $name = $torrentDone[2];
					            $sizeFull = $torrentDone[3];
					            $sizeDone = $torrentDone[5];
					            $percentage = $torrentDone[4]/10;
					            $speed = $torrentDone[9];

					            echo "<div class='torrent'>";
					            echo $name;
					            echo "<progress value='".$sizeDone."' max='".$sizeFull."'></progress>";
					            echo "<span class='stats'>";
					            echo ByteSize($sizeDone)." / ".ByteSize($sizeFull)." (".$percentage."%)";
					            echo " @ " .ByteSize($speed);
					            echo "</span>";
					            echo "</div>";
					        }

					    }

					?>
				</div>
			</div>
		</section>
		<?php endif; ?>

		<?php if($config['transmission']) : ?>	
		<section class="clearfix">
			<a href="<?= $transmissionURL; ?>" title="Transmission" class="actionButton big transmission"><span>Transmission</span></a>

			<div class="downloadFrame">
				<div class="downloadPage downloadPageCurrent">
					<h2>Currently Downloading</h2>
					<?php
						//$transmissionAPI = new TransmissionRPC($transmissionURL."/transmission/rpc", null, null, true);

$rpc = new TransmissionRPC();
$rpc->url = $transmissionURL."/transmission/rpc";

					?>
				</div>
			</div>
		</section>
		<?php endif; ?>

		<?php ## Wifi ?>
		<?php if( $config['showWifi'] ) : ?>
			<div class="wifi">
				<h2>Wifi Password for <?= $config['wifiName'] ?></h2>
				<big><?= $config['wifiPassword']; ?></big>
			</div>
		<?php endif; ?>

		<div class="secondaryButtons clearfix">

			<?php if( $config['showTrailers'] ) : ?>
			<a href="http://www.hd-trailers.net/" target="_blank" class="actionButton small icon iconTrailer"><span>Watch Trailers</span></a>
			<?php endif; ?>

			<?php if ( !empty($config['bookmarks']) ) {
				foreach ($config['bookmarks'] as $bookmark) {

					// Check for custom icon, otherwise use favicon from website
					if($bookmark['icon']) {
						$icon = $bookmark['icon'];
					} else {
						$icon = $bookmark['url']."/favicon.ico";
					}
					
					echo "<a href='".$bookmark['url']."' target='_blank' class='actionButton small icon'><span style='background-image: url(".$icon.");'>".$bookmark['label']."</span></a>";

				}
			}
			?>

		</div>

		<div class="openvpn">
		    <h2>External IP:</h2>
		    <?php ## Get external IP
		        exec('wget -qO- icanhazip.com', $ipaddy);
                            echo implode("\n", $ipaddy);
		    ?>
		</div>

		<div>
		<?php ## OpenVPN running check
			exec("pgrep openvpn", $output, $return);
			if ($return == 0) {
			    echo "<div class='status-green'>";
			    echo "OpenVPN is running\n";
			} else {
			    echo "<div class='status-red'>";
                            echo "OpenVPN not running\n";
		 	}
	       ?>
		</div>

                <script type="text/javascript">

		$(function worker(){
		    $.ajaxSetup ({
		        cache: false,
		        complete: function() {
		          setTimeout(worker, 2000);
		        }
		    });
//		    var ajax_load = "<img src='http://apps2.rfriedlein.com/intranet/images/loading.gif' alt='loading...' />";
		    var loadUrl = "http://apps2.rfriedlein.com/getinterface.php";
		    $("#interface-stats").load(loadUrl);
		});

                </script>


		<div class="interface"> <h2>Network Stats:</h2>
                <div id="interface-stats">  </div>
		<div>
		<?php ## Ending check for all-disabled ?>
		<?php endif; ?>

	</body>
</html>
