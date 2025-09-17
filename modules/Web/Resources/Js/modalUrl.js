// Ein Stack-Array, um die URL-Historie des Modals zu speichern
let urlStack = [];
let titleStack = [];

// Listener für das Öffnen eines Modals
Livewire.on('update-url', (event) => {
    const newUrl = event.url;

    // Speichere die aktuelle URL im Stack, bevor wir eine neue URL pushen
    urlStack.push(window.location.href);

    // Speichere den aktuellen Title im Stack.
    titleStack.push(document.title);

    // Ändere die URL im Browser
    history.pushState({}, '', newUrl);
});

// Listener für das Schließen eines Modals
Livewire.on('revert-url', () => {
    // Überprüfe, ob es eine vorherige URL im Stack gibt
    if (urlStack.length > 0) {
        // Hole die letzte URL aus dem Stack und entferne sie
        const revertedUrl = urlStack.pop();
        const revertedTitle = titleStack.pop();

        // Setze die URL auf den letzten Eintrag im Stack zurück
        history.pushState({}, '', revertedUrl);
        document.title = revertedTitle;
    }
});
