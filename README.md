# Bookshelf

Bookshelf ist ein leichtgewichtiges PHP-Framework zur Erstellung von REST-APIs mit MySQL. Es richtet sich an Entwickler, die eine einfache, strukturierte Grundlage für ihre API-Projekte suchen.

## Features

- **Routing:**  
  Ein Front-Controller (Klasse `App`) parst die URL, vergleicht sie mit vordefinierten Routen und leitet die Anfrage an den entsprechenden Controller weiter.

- **Modulare Struktur:**  
  Trennung von Logik, Controllern und Konfiguration in unterschiedliche Ordner, was die Wartung und Erweiterung erleichtert.

- **GET-Parameter-Validierung:**  
  Einfache Validierung und Filterung von GET-Parametern (z. B. `size` und `page`) mithilfe von PHP-Filtern.

- **Erweiterbarkeit:**  
  Basis für weitere Features wie Autoloading, Namespaces, erweiterte Error- und Exception-Handling oder API-Dokumentation (z. B. mit Swagger/OpenAPI).

## Voraussetzungen

- **PHP:**  
  PHP 8.0 oder höher wird empfohlen.
  
- **MySQL:**  
  Eine MySQL-Datenbank für die Datenspeicherung.

- **Webserver:**  
  Ein Webserver (z. B. Apache, Nginx oder der PHP-interne Server) zur Ausführung der API.

## Installation

1. **Repository klonen:**

   ```bash
   git clone https://github.com/daniel-rp-wagner/bookshelf.git
   cd bookshelf
   ```

2. **Konfiguration:**  
   - Erstelle oder passe die Konfigurationsdateien (z. B. `routes.php` im Ordner `app`) an deine Bedürfnisse an.
   - Erstelle eine `.env`-Datei und trage diese in die `.gitignore` ein.

3. **Datenbank einrichten:**  
   Richte deine MySQL-Datenbank ein und passe die Verbindungseinstellungen in deiner Konfiguration an.

## Nutzung

- **Lokaler Entwicklungsserver:**  
  Du kannst den PHP-internen Server für die lokale Entwicklung nutzen:

  ```bash
  php -S localhost:8000 -t public
  ```

  Anschließend ist die API unter [http://localhost:8000](http://localhost:8000) erreichbar.

- **API-Aufrufe:**  
  Die Routen und zugehörigen Controller werden in der Datei `app/routes.php` definiert. Passe diese Datei an, um neue Endpunkte zu erstellen oder bestehende zu erweitern.

## Ordnerstruktur (Beispiel)

```
bookshelf/
├── app/
│   ├── config/           # Konfigurationsdateien
│   ├── controllers/      # Enthält die Controller-Klassen
│   ├── core/             # Anwendungsdateien
│   ├── models/           # Enthält die Model-Klassen (z. B. für Datenbankzugriff)
│   ├── views/            # HTML-Gerüst
│   └── routes.php        # Definiert die API-Routen
├── public/               # Öffentlicher Ordner (Entry Point, z. B. index.php)
│   └── index.php         # Einstiegspunkt in die Anwendung
└── README.md             # Diese Datei
```

## Weiterentwicklung

- **Tests:**  
  Mit PHPUnit können Unit-Tests zur Sicherstellung der Funktionalität hinzugefügt werden.
