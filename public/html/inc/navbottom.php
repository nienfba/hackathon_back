<div id="navBottom" class="container-fluid">
  <div class="row">
    <div class="col-md-4 mt-2">
      <form>
        <div class="form-row align-items-center w-80">
          <label class="sr-only" for="inlineFormInputGroup">hashtag</label>
          <div class="input-group mb-2">
            <div class="input-group-prepend">
              <div class="input-group-text borderRoundL p-1">#</div>
            </div>
            <input type="text" class="form-control" id="inputHashtag" placeholder="hashtag">
            <div class="input-group-prepend">
              <div class="input-group-text borderRoundR">
                <button id="hashtag" class="btn" type="button" name="buttonHashtag">OK</button>
              </div>

            </div>
          </div>
        </div>
      </form>

    </div>
    <div class="col-md-2 mt-2">

    </div>
    <div class="col-md-6 mt-2">
      <div class="row float-right">

        <button type="button" id="showRight" class="btn btn-info mr-2" style="border-radius:100px">
                  <i class="fa fa-compass"></i>
              </button>
        <button type="button" class="btn btn-secondary mr-2" style="border-radius:100px">
                  <i class="fa fa-camera-retro"></i>
              </button>
        <button type="button" id="showOverlay" class="btn btn-success mr-2" style="border-radius:100px">
                  <i class="fa fa-heart"></i>
              </button>
      </div>
    </div>
  </div>
</div>
<div id="navBotMob" class="container-fluid ">
  <div class="row text-center categoryHide">
    <div class="col-6">
      <div class="mt-2">
        <i></i> <a><p>Musique</p></a>
      </div>
      <div class="mt-2">
        <i></i> <a><p>Divertissement</p></a>
      </div>
      <div class="mt-2">
        <i></i> <a><p>Shopping</p></a>
      </div>
      <div class="mt-2">
        <i></i> <a><p>Restauration</p></a>
      </div>
    </div>
    <div class="col-6">
      <div class="mt-2">
        <i></i> <a><p>Promenade</p></a>
      </div>
      <div class="mt-2">
        <i></i> <a><p>Logement</p></a>
      </div>
      <div class="mt-2">
        <i></i> <a><p>Sport</p></a>
      </div>
      <div class="mt-2">
        <i></i> <a><p>Culture</p></a>
      </div>
    </div>

  </div>



  <div class="row">
    <div class="col-4 mt-2">
      <form>
        <div class="form-row align-items-center w-80">
          <label class="sr-only" for="inlineFormInputGroup">hashtag</label>
          <div class="input-group mb-2">
            <div class="input-group-prepend">
              <div class="input-group-text borderRoundL p-1">#</div>
            </div>
            <input type="text" class="form-control" id="inputHashtag" placeholder="hashtag">
            <div class="input-group-prepend">
                <div class="input-group-text borderRoundR"><button id="hashtag">OK</button></div>
            </div>
          </div>
        </div>
      </form>
    </div>

    <div class="col-4 mt-2 d-inline-block align-center btnMid p-0">
      <button type="button" class="btn btn-danger border-circle question" data-container="body" data-toggle="popover" data-placement="top">
            <i class="fa fa-question"></i>
        </button>
      <button type="button" class="btn btn-primary border-circle " >
            <i class="fa fa-crosshairs"></i>
        </button>
    </div>

    <div class="col-4 mt-2">
      <button type="button" name="filtre" class="btn filtreBy float-right">Filtrer par</button>
      <div class="" style="display:none;">
        <button type="button" id="showRight" class="btn btn-info mr-2" style="border-radius:100px">
            <i class="fa fa-compass"></i>
        </button>
        <button type="button" class="btn btn-secondary mr-2" style="border-radius:100px">
            <i class="fa fa-camera-retro"></i>
        </button>
        <button type="button" id="showOverlay" class="btn btn-success mr-2" style="border-radius:100px">
            <i class="fa fa-heart"></i>
        </button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function () {


        //alert('ok');

        $('.question').on('mouseover', () => {
            $('.question').popover({
                container: 'body',
                content: "Un doute ? Besoin d'info supplementaire ? Demandez à la comunuté !"
            });
            $('.question').popover('show');
            //alert('ok');
        })
        $('.question').on('mouseleave', () => {

            $('.question').popover('hide');
            //alert('ok');
        })

    });
</script>
