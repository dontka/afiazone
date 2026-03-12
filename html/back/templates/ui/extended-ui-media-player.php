<?php
$pageTitle = 'Plyr - Extended UI | Vuexy - Bootstrap Admin Template';
$vendorStyles = ['/assets/vendor/libs/plyr/plyr.css'];
$vendorScripts = ['/assets/vendor/libs/plyr/plyr.js'];
$additionalScripts = ['/assets/js/extended-ui-media-player.js'];
ob_start();
?>
              <h4 class="py-3 mb-4"><span class="text-muted fw-light">Extended UI /</span> Plyr</h4>

              <div class="row">
                <!-- Video Player -->
                <div class="col-12 mb-4">
                  <div class="card">
                    <h5 class="card-header">Video</h5>
                    <div class="card-body">
                      <video
                        class="w-100"
                        poster="https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-HD.jpg"
                        id="plyr-video-player"
                        playsinline
                        controls>
                        <source
                          src="https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-576p.mp4"
                          type="video/mp4" />
                      </video>
                    </div>
                  </div>
                </div>
                <!-- /Video Player -->

                <!-- Audio Player -->
                <div class="col-12">
                  <div class="card">
                    <h5 class="card-header">Audio</h5>
                    <div class="card-body">
                      <audio class="w-100" id="plyr-audio-player" controls>
                        <source src="/assets/audio/Water_Lily.mp3" type="audio/mp3" />
                      </audio>
                    </div>
                  </div>
                </div>
                <!-- /Audio Player -->
              </div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';
