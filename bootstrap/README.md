# Bootstrap Kütüphanesi

Bu klasör Bootstrap CSS framework'ünün dosyalarını içerir.

## Dosya Yapısı

```
bootstrap/
├── css/
│   └── bootstrap.min.css
├── js/
│   └── bootstrap.min.js
└── README.md
```

## Kullanım

### HTML'de Bootstrap'i Dahil Etme

```html
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bootstrap Projesi</title>
    
    <!-- Bootstrap CSS -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Sayfa içeriği buraya -->
    
    <!-- Bootstrap JavaScript (gerekirse) -->
    <script src="bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
```

### CDN Kullanımı (Alternatif)

Eğer yerel dosyalar yerine CDN kullanmak isterseniz:

```html
<!-- Bootstrap CSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap JavaScript CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
```

## Güncelleme

Mevcut dosyalar placeholder dosyalardır. Gerçek Bootstrap dosyalarını indirmek için:

1. https://getbootstrap.com/ adresine gidin
2. "Download" bölümünden dosyaları indirin
3. İndirilen dosyaları bu klasördeki ilgili dosyalarla değiştirin

## Bootstrap Versiyonu

Bu proje Bootstrap v5.3.2 kullanmaktadır.
