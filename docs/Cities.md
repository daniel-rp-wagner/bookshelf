# Cities API – Nutzerdokumentation

Die Cities API ermöglicht es Ihnen, Stadt-Datensätze auf einfache und benutzerfreundliche Weise zu verwalten. Mit dieser API können Sie neue Städte anlegen, bestehende Einträge aktualisieren, detaillierte Informationen zu einer Stadt abrufen und Städte entfernen, wenn sie nicht mehr benötigt werden.

## Überblick

Die Cities API bietet Ihnen folgende Funktionen:

- **Städte auflisten:**  
  Sie können eine umfassende Liste aller Städte abrufen. Zusätzlich besteht die Möglichkeit, die Ergebnisse nach Ländern oder anderen Kriterien zu filtern, sodass nur die relevanten Städte angezeigt werden.

- **Neue Stadt erstellen:**  
  Legen Sie neue Stadtdatensätze an und übermitteln Sie dabei wichtige Informationen wie offizielle Namen, Anzeige-Namen, Länderdaten sowie geografische Koordinaten.

- **Stadt-Details abrufen:**  
  Erhalten Sie detaillierte Informationen zu einer bestimmten Stadt, indem Sie deren eindeutige ID verwenden.

- **Stadt-Daten aktualisieren:**  
  Ändern Sie vorhandene Stadtdatensätze, um sicherzustellen, dass alle Informationen stets aktuell und korrekt sind.

- **Stadt löschen:**  
  Entfernen Sie Städte, die nicht mehr relevant sind, um Ihre Datenbank sauber und aktuell zu halten.

## Hauptfunktionen

### Städte auflisten

- **Filterung:**  
  Sie können Ihre Liste mithilfe von Filtern eingrenzen, beispielsweise durch Angabe des ISO-Codes eines Landes. So konzentrieren Sie sich nur auf die Städte einer bestimmten Region.
  
- **Paginierung:**  
  Bei großen Datenmengen können Sie die Ergebnisse paginieren. Dadurch wird die Datenpräsentation in handhabbare Abschnitte unterteilt.

### Neue Stadt erstellen

- **Grundlegende Angaben:**  
  Erfassen Sie die grundlegenden Informationen wie den offiziellen Namen, den Anzeige-Namen und den Typ der Stadt.
  
- **Länderverknüpfung:**  
  Ordnen Sie die Stadt dem entsprechenden Land zu, indem Sie den ISO-Code des Landes angeben.
  
- **Geografische Koordinaten:**  
  Übermitteln Sie die Breiten- und Längengrade der Stadt, um ihre exakte Position auf einer Karte festzulegen.
  
- **Lokalisierte Namen:**  
  Die API unterstützt mehrere Sprachen, sodass Sie lokale Namen hinzufügen können. Dies gewährleistet, dass die Stadtdaten entsprechend der bevorzugten Sprache des Nutzers angezeigt werden.

### Stadt-Details abrufen

- **Umfassende Informationen:**  
  Erhalten Sie detaillierte Angaben zu einer Stadt, wie z. B. deren Namen in verschiedenen Sprachen, Länderzugehörigkeit, Typ und geografische Koordinaten.
  
- **Sprachunterstützung:**  
  Sie können die Stadtinformationen in der gewünschten Sprache abrufen, sodass die Daten kontextbezogen und benutzerfreundlich sind.

### Stadt-Daten aktualisieren

- **Flexible Aktualisierung:**  
  Passen Sie beliebige Aspekte eines Stadtdatensatzes an. Ob es darum geht, Namen, Länderinformationen oder geografische Koordinaten zu aktualisieren – die API erlaubt Ihnen, die Daten nach Bedarf zu ändern.
  
- **Teilweise oder vollständige Aktualisierungen:**  
  Sie haben die Möglichkeit, entweder einzelne Felder zu aktualisieren oder den gesamten Datensatz zu ersetzen, je nach Ihren Anforderungen.

### Stadt löschen

- **Datenbereinigung:**  
  Entfernen Sie Städte, die veraltet oder nicht mehr relevant sind, um Ihre Datenbank aktuell und übersichtlich zu halten.
  
- **Kontrollierte Löschung:**  
  Die API sorgt für eine sichere Löschung, sodass verwandte Daten angemessen behandelt werden und kein ungewollter Datenverlust entsteht.

## Anwendungsfälle

- **Verwaltungstools:**  
  Integrieren Sie die Cities API in Ihr Admin-Dashboard, um Stadtdatensätze einfach zu verwalten – von der Erstellung über die Aktualisierung bis hin zur Löschung.
  
- **Standortbasierte Dienste:**  
  Nutzen Sie präzise und aktuelle Stadtdaten in Anwendungen wie Reiseführern, Kartendiensten oder lokalen Suchmaschinen.
  
- **Mehrsprachige Plattformen:**  
  Bieten Sie lokalisierten Inhalt an, indem Sie die mehrsprachige Unterstützung der API nutzen, sodass Stadtnamen und Details entsprechend der Sprache des Nutzers dargestellt werden.
