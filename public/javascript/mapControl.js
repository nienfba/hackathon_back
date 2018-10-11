var lat;
var lng;

/*Exemple
var customControl = L.Control.extend({
  options: {
    position: 'topright'
  },

  onAdd: function(map) {
    var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');

    container.style.backgroundColor = 'white';
    //container.style.backgroundImage = "url(http://t1.gstatic.com/images?q=tbn:ANd9GcR6FCUMW5bPn8C4PbKak2BJQQsmC-K9-mbYBeFZm1ZM2w2GRy40Ew)";
    container.style.backgroundSize = "30px 30px";
    container.style.width = '26px';
    container.style.height = '100px';

    container.onclick = function() {
      console.log('buttonClicked');
    }

    return container;
  }
});

map.addControl(new customControl());*/


/**
 *Function Question
 */
var questionControl = L.Control.extend({
  options: {
    position: 'topleft'
  },
  onAdd: function(map) {
    var containerQ = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');
    containerQ.style.backgroundColor = 'white';
    containerQ.style.width = '26px';
    containerQ.style.height = '26px';
    containerQ.title = 'question';
    containerQ.style.backgroundImage = "url(./media/mapicons/question.png)";
    containerQ.style.backgroundSize = "26px 26px";

    containerQ.onclick = function() {
      //alert('ici');
      var center = map.getCenter();

      var iconQuestion = L.icon({
        iconUrl: './media/mapicons/question.png'});

      var markerQ = new L.marker(center, {
          draggable: 'true',
          icon: iconQuestion
        })
        .addTo(map)
        .bindPopup('<input class="titre" style="width:100%; color:black;"><br><textarea class="description" style="width:100%" rows="5"></textarea><br><button id="submitQ" onclick(submitQuestion();)>poser votre question</button>')
        .openPopup();
      markerQ.on("dragend", function() {
        this.openPopup();
      })
    }
    $('#submitQ').on("click", () => {
      markerQ.dragging.disable();
    });

    return containerQ;
  }
});
map.addControl(new questionControl());

/**
 *END Function Question
 */

/**
 *Function Geolocalisation
 */
var geoloc = L.Control.extend({
  options: {
    position: 'topleft'
  },

  onAdd: function(map) {
    var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');
    container.title = "Geolocalisation";
    container.style.backgroundColor = 'white';
    container.style.backgroundSize = "26px 26px";
    container.style.width = '26px';
    container.style.height = '26px';
    container.style.backgroundImage = "url(./media/mapicons/localization.png)";
    container.onmouseover = function() {
      container.style.backgroundColor = 'tomato';
    }
    container.onmouseout = function() {
      container.style.backgroundColor = 'white';
    }
    container.onclick = function() {
      map.locate({
        setView: true,
        maxZoom: 16
      });
    }

    return container;
  }
});

function onLocationFound(e) {
  var radius = e.accuracy / 2;

  var iconGeoloc = L.icon({
        iconUrl: './media/mapicons/localization.png'});

  L.marker(e.latlng, {icon:iconGeoloc}).addTo(map)
    .bindPopup("Vous êtes à " + radius + " métres de ce point!").openPopup();

  L.circle(e.latlng, radius).addTo(map);
}

map.on('locationfound', onLocationFound);
map.addControl(new geoloc());
/**
 *END Function Geolocalisation
 */

/**
 *Function SearchBar Hashtag
 */
var hashtagControl = L.Control.extend({
  options: {
    position: 'topright'
  },

  /*onAdd: function(map) {
    var containerHashtag = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');

    containerHashtag.style.backgroundColor = 'white';
    containerHashtag.style.backgroundSize = "30px 30px";
    containerHashtag.style.width = '260px';
    containerHashtag.style.height = '20px';

    containerHashtag.onclick = function() {
      alert('buttonClicked');
    }

    return containerHashtag;
  }*/
});

map.addControl(new hashtagControl());
/**
 *END Function SearchBar Hashtag
 */
