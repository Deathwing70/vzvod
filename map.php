<?php
$title = 'Карта';
require_once ('static/inc/header.php');
require_once('static/src/map-handler.php');
dbMarkers();
?>

</div>
<div class="map">
    <div id="map"></div>
</div>
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Успешно!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Маркер добавлен на карту!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<!--Загрузка Leaflet-->
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.3/dist/leaflet.js"></script>
<!--Загрузка Leaflet - ruler-->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/gokertanrisever/leaflet-ruler@master/src/leaflet-ruler.css" integrity="sha384-P9DABSdtEY/XDbEInD3q+PlL+BjqPCXGcF8EkhtKSfSTr/dS5PBKa9+/PMkW2xsY" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/gh/gokertanrisever/leaflet-ruler@master/src/leaflet-ruler.js" integrity="sha384-N2S8y7hRzXUPiepaSiUvBH1ZZ7Tc/ZfchhbPdvOE5v3aBBCIepq9l+dBJPFdo1ZJ" crossorigin="anonymous"></script>
<!--Загрузка JS-Bootstrap-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<!--Загруза скрипта карты-->
<script src="static/scripts/map.js"></script>
<form id="myForm">
    <label for="lat">Название:</label>
    <input type="text" id="name" name="name" required>

    <label for="lat">Описание:</label>
    <input type="text" id="description" name="description" required>

    <label for="lat">Широта:</label>
    <input type="text" id="x" name="x" required>

    <label for="lng">Долгота:</label>
    <input type="text" id="y" name="y" required>

    <button type="submit">Отправить</button>
</form>
<script>
  // Обработчик клика по карте для добавления нового маркера
  let marker;
  let form = document.getElementById('myForm');
  let markers = <?php echo json_encode(dbMarkers()) ?>;
  var cities = L.featureGroup().addTo(map)

  function onMapClick(e) {

      if (marker) {
          map.removeLayer(marker);
      }

      let x = e.latlng.lat;
      let y = e.latlng.lng;


      // Добавление нового маркера на карту
      marker = L.marker([x, y]).addTo(cities);

      // получение ссылки на поле ввода широты
      let latInput = form.elements['x'];
      // получение ссылки на поле ввода долготы
      let lngInput = form.elements['y'];
      // Установка значений полей ввода широты и долготы
      latInput.value = x;
      lngInput.value = y;

  }
  // Создание маркеров на карте для каждого маркера в базе данных
  function loadMarkers() {
      cities.clearLayers();
      for (let i = 0; i < markers.length; i++) {
          let marker = L.marker([markers[i].x, markers[i].y], {title: markers[i].name}).addTo(cities);
          let popupContent = '<b>' + markers[i].name + '</b><br/>' + markers[i].description + '<br/>' + '</p><button onClick="deleteMarker(' + markers[i].id + ')">Удалить</button>';
          marker.bindPopup(popupContent);
      }
  }

  loadMarkers()

  map.on('click', onMapClick);

  //Функция удаления маркеров
  function deleteMarker(id) {

      let marker = null;
      for (let i = 0; i < markers.length; i++) {
          if (markers[i].id == id) {
              marker = markers[i];
              markers.splice(i, 1);
              break;
          }
      }
      if (marker) { // Проверяем, что маркер существует
          cities.removeLayer(marker); // Удаляем маркер из карты
          let xhr = new XMLHttpRequest();
          xhr.open('POST', 'static/src/map-delete.php', true);
          xhr.setRequestHeader('Content-Type', 'application/json');
          xhr.onreadystatechange = function() {
              if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                  // Удаляем маркер с карты
                  map.eachLayer(function(layer) {
                      if (layer instanceof L.Marker && layer.options.id === id) {
                          map.removeLayer(layer);
                      }
                  });
                  // Загружаем обновленные данные и отображаем их на карте
                  loadMarkers();
              }
          };
          xhr.send(JSON.stringify({id: marker.id}));
      }
  }

  // добавление обработчика события отправки формы
  form.addEventListener('submit', function(event) {
      // отмена стандартного действия браузера при отправке формы
      event.preventDefault();

      // создание объекта XMLHttpRequest
      let xhr = new XMLHttpRequest();

      // установка обработчика события изменения состояния запроса
      xhr.onreadystatechange = function() {
          if (xhr.readyState === XMLHttpRequest.DONE) {
              if (xhr.status === 200) {
                  // обработка успешного ответа от сервера
                  var responseData = JSON.parse(xhr.responseText);
                  // Очистка формы
                  form.reset();
                  // Использование данных, отправленных из PHP
                  let name = responseData.name;
                  let x = responseData.x;
                  let y = responseData.y;
                  let description = responseData.description;
                  let id = responseData.id;

                  markers.push({
                      name: name,
                      x: x,
                      y: y,
                      description: description,
                      id: id
                  });

                  loadMarkers()
                  showSuccessModal();

              } else {
                  // обработка ошибки ответа от сервера
                  console.error('Произошла ошибка при отправке данных на сервер');
              }
          }
      };

      // получение данных формы
      let formData = new FormData(form);

      // отправка данных формы на сервер методом POST
      xhr.open('POST', 'static/src/map-handler.php');
      xhr.send(formData);
  });

  function showSuccessModal() {
      let successModal = new bootstrap.Modal(document.getElementById('successModal'), {
          keyboard: false
      });
      successModal.show();
  }


</script>

</body>
</html>