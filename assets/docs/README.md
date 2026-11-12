# Osteria del Borgo — Heritage Collection

Prototipo premium, monopagina, di un’osteria tradizionale a Parma. Identità e informazioni immaginarie, interfaccia interamente in italiano.

## Avvio

Richiede **PHP 8.0 o superiore**. Non sono necessari Composer, Node.js, compilazione, chiavi API o una banca dati.

Dalla radice del progetto:

```sh
php -S 0.0.0.0:8000
```

Aprire la porta 8000 nel browser. Il server integrato PHP è destinato soltanto allo sviluppo. In produzione utilizzare un normale hosting PHP con Apache o Nginx/PHP-FPM e HTTPS.

La radice contiene esclusivamente `index.php` e `assets/`. Il file README è conservato qui per rispettare questa struttura.

```text
index.php                 Dati editoriali, catalogo e pagina HTML5
assets/
  css/style.css           Sistema grafico e layout responsive
  js/app.js               Moduli Vanilla JS e orchestrazione GSAP
  images/                 Fotografie WebP ottimizzate e grana sottile
  fonts/                  Caratteri WOFF2 locali e relative licenze
  vendor/                 GSAP, ScrollTrigger, Lenis e Swiper locali
  docs/                   Istruzioni, crediti e piano di verifica
  favicon.svg             Marchio vettoriale originale
```

## Funzionalità

- Navigazione ad ancore, intestazione trasparente e sfocata allo scorrimento, menu mobile con focus confinato.
- Storia, statistiche animate, firma e presentazione dello chef.
- Sei categorie di menù accessibili con mouse, tocco e tastiera; catalogo centralizzato in PHP.
- Cantina, galleria fotografica asimmetrica, carosello Swiper e lightbox con zoom, frecce e tasto Esc.
- Prenotazione: campi obbligatori, telefono, email, date nei prossimi 90 giorni, chiusura del lunedì, esclusione degli orari già trascorsi, sala/terrazza, note e conferma illustrativa.
- Consegna e asporto; aggiunta, modifica delle quantità (1–20) e rimozione dei piatti; carrello laterale e riepilogo.
- Calcoli in centesimi interi: consegna € 4, gratuita da € 50; ritiro gratuito.
- Ordine simulato con indirizzo obbligatorio per la consegna, modalità di pagamento al ritiro/alla consegna e conferma senza addebiti.
- Recensioni illustrative, FAQ a fisarmonica, mappa vettoriale, contatti, newsletter simulata e finestre informative.
- Immagini lazy-loaded, caricamento prioritario della foto principale, font e librerie locali. Nessuna richiesta a CDN durante la normale navigazione.
- Rispetto di `prefers-reduced-motion`, pulsanti etichettati, errori associati ai campi, tab accessibili, focus visibile e dialoghi HTML nativi.
- Contenuti e menù completo leggibili senza JavaScript; i moduli vengono abilitati solo dopo l’inizializzazione dei gestori front-end.

## Cosa viene simulato

**Nessuna prenotazione, email, iscrizione, preparazione, consegna o transazione reale viene eseguita.** Non esistono endpoint applicativi, autenticazione o pannelli amministrativi.

I dati personali digitati nei moduli rimangono nella memoria temporanea della pagina e non sono salvati in `localStorage`. Solo il carrello (codici prodotto, quantità e modalità) viene memorizzato sul dispositivo con la chiave `osteria-del-borgo:cart:v1`. Se il browser blocca l’archiviazione locale, il carrello continua a funzionare in memoria.

I prezzi includono valori illustrativi. Le selezioni della terrazza sono richieste soggette alla disponibilità stagionale indicata nelle FAQ. Le fasce orarie della consegna sono illustrative.

## Personalizzazione

1. Modificare `$restaurant`, `$menu`, `$gallery`, `$reviews` e `$faqs` all’inizio di `index.php`.
2. Sostituire i testi di storia e chef e aggiornare i recapiti e gli orari.
3. Sostituire i file immagine mantenendone i nomi oppure aggiornare le chiavi nel catalogo. Preferire WebP, con una foto principale non oltre 1920 px e foto dei piatti intorno a 900 px.
4. Personalizzare colori, dimensioni e tipografia nei token `:root` di `assets/css/style.css`.
5. Adeguare le regole di consegna e le date in `assets/js/app.js` quando si collega un vero servizio.

Prima di una pubblicazione commerciale è indispensabile sostituire tutti i dati fittizi, verificare diritti fotografici e informazioni alimentari, predisporre un’informativa privacy reale e implementare un backend sicuro con validazione server-side, gestione delle prenotazioni e provider di pagamento. La validazione del browser non è un sistema di sicurezza per transazioni reali.

## Compatibilità

Browser moderni con supporto a `<dialog>`, Grid/Flexbox, WebP e `Intl`: Chrome, Edge, Safari e Firefox aggiornati. I test automatici del prototipo sono stati effettuati con Chromium; i browser reali e i dispositivi di produzione richiedono comunque una verifica finale.

I file nella cartella `vendor/` e i font mantengono i rispettivi avvisi e licenze. Consultare `CREDITI.md` e `../vendor/NOTICE.md`.
