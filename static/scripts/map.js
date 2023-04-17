let map = L.map('map', {
    crs: L.CRS.Simple, // указываем простую систему координат
    minZoom: -2, // минимальный зум
    maxZoom: 4, // максимальный зум
});

L.Control.Watermark = L.Control.extend({
    onAdd: function(map) {
        var img = L.DomUtil.create('img');

        img.src = 'static/images/vzvod-watermark.png';
        img.style.width = '100px';

        return img;
    },

    onRemove: function(map) {
        // Nothing to do here
    }
});
L.control.watermark = function(opts) {
    return new L.Control.Watermark(opts);
}

let bounds = [[0, 0], [4320, 7680]]; // координаты верхнего левого и нижнего правого углов изображения в пикселях
let image = L.imageOverlay('static/images/vzvod-map.jpeg', bounds).addTo(map);

map.fitBounds(bounds); // автоматически масштабируем карту до размеров изображения

map.setMaxBounds(bounds); // задаем ограничивающий прямоугольник для перемещения по карте

L.control.watermark({ position: 'bottomleft' }).addTo(map);
