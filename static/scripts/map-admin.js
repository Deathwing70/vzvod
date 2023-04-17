let marker;
let form = document.getElementById('mapAddForm');
var cities = L.featureGroup().addTo(map)

//Обработчик клика по карте для добавления нового маркера
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
    latInput.value = x.toFixed(0);
    lngInput.value = y.toFixed(0);

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
