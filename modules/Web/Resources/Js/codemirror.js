// resources/js/web/codemirror.js

import { EditorView } from "@codemirror/view";
import { EditorState } from "@codemirror/state";
import { basicSetup } from "codemirror";

import { html } from "@codemirror/lang-html";
import { javascript } from "@codemirror/lang-javascript";
import { css } from "@codemirror/lang-css";
import { autocompletion } from "@codemirror/autocomplete";

import { oneDark } from "@codemirror/theme-one-dark";

function getCustomThemeWhite() {
    return EditorView.theme({
        "&": {
            minHeight: "200px",
            maxHeight: "500px",
            border: "1px solid #E5E7EB",
            borderRadius: "10px"
        },
        ".cm-gutters": {
            borderRadius: "10px 0 0 10px",
            backgroundColor: "transparent",
            borderRight: "1px solid #EEEEEE"
        }
    });
}

function getCustomThemeDark() {
    return EditorView.theme({
        "&": {
            minHeight: "200px",
            maxHeight: "500px",
            border: "1px solid #4A5565",
            borderRadius: "10px"
        },
        ".cm-gutters": {
            borderRadius: "10px 0 0 10px",
            backgroundColor: "transparent",
            borderRight: "1px solid #4A5565"
        }
    });
}

/**
 * Wartet auf korrekte Theme-Erkennung
 */
function waitForTheme(maxAttempts = 5) {
    return new Promise((resolve) => {
        let attempts = 0;

        function checkTheme() {
            const isDark = document.documentElement.classList.contains("dark");
            const hasClass = document.documentElement.classList.length > 0;

            attempts++;

            // Bei DOMContentLoaded ist das Theme meist schon korrekt
            if (document.readyState === 'complete') {
                resolve(isDark);
                return;
            }

            // Wenn Dark-Klasse vorhanden oder mehrere Versuche gemacht
            if (isDark || attempts >= maxAttempts) {
                resolve(isDark);
                return;
            }

            // Nächster Versuch nach 10ms
            setTimeout(checkTheme, 100);
        }

        checkTheme();
    });
}

/**
 * Liefert die Extensions basierend auf data-languages Attribut
 */
async function getExtensionsFromLanguages(languages, hiddenInput) {
    // Warte auf korrekte Theme-Erkennung
    const isDark = await waitForTheme();

    const exts = [basicSetup, autocompletion()];

    if (isDark) {
        // Dark Mode = oneDark + deine Dark-Anpassungen
        exts.push(oneDark);
        exts.push(getCustomThemeDark());
    } else {
        // Light Mode = nur deine Light-Anpassungen
        exts.push(getCustomThemeWhite());
    }

    languages.forEach(lang => {
        switch (lang.trim()) {
            case "html":
                exts.push(html());
                break;
            case "javascript":
                exts.push(javascript());
                break;
            case "js":
                exts.push(javascript());
                break;
            case "css":
                exts.push(css());
                break;
            case "scss":
                exts.push(css());
                break;
        }
    });

    if (hiddenInput) {
        exts.push(
            EditorView.updateListener.of(update => {
                if (update.docChanged) {
                    hiddenInput.value = update.state.doc.toString();
                    hiddenInput.dispatchEvent(new Event("input", { bubbles: true }));
                }
            })
        );
    }

    return exts;
}

/**
 * Initialisiert alle CodeMirror-Editoren auf der Seite
 */
async function initEditors() {
    const containers = document.querySelectorAll(".code-editor");

    for (const container of containers) {
        if (container.dataset.initialized) continue;
        container.dataset.initialized = "true";

        const hiddenInput = container.parentElement.querySelector("input[type=hidden]");
        const startDoc = hiddenInput?.value || "";

        // ⚡️ jetzt data-languages statt data-mode
        const langs = (container.dataset.languages || "").split(",");
        const extensions = await getExtensionsFromLanguages(langs, hiddenInput);

        const view = new EditorView({
            state: EditorState.create({
                doc: startDoc,
                extensions
            }),
            parent: container
        });

        // Klick auf Container → Fokus in Editor
        container.addEventListener("click", () => {
            view.focus();
        });
    }
}

/**
 * Theme-Change Observer für Live-Updates
 */
function setupThemeObserver() {
    let currentTheme = document.documentElement.classList.contains("dark");

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const newTheme = document.documentElement.classList.contains("dark");

                // Nur wenn sich das Theme tatsächlich geändert hat
                if (newTheme !== currentTheme) {
                    currentTheme = newTheme;
                    // Alle Editoren als nicht initialisiert markieren
                    document.querySelectorAll(".code-editor").forEach(container => {
                        delete container.dataset.initialized;
                        // Container leeren
                        container.innerHTML = '';
                    });

                    // Nach kurzer Verzögerung neu initialisieren
                    setTimeout(() => {
                        initEditors();
                    }, 100);
                }
            }
        });
    });

    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });
}

// Init bei Seite geladen
document.addEventListener("DOMContentLoaded", () => {
    setupThemeObserver();
    // Bei DOMContentLoaded ist das Theme meist schon korrekt, keine Verzögerung nötig
    initEditors();
});

// Init nach Livewire DOM Updates mit Verzögerung
document.addEventListener("livewire:navigated", () => {
    // Kurze Verzögerung für Theme-Setup
    setTimeout(initEditors, 100);
});

document.addEventListener("livewire:update", () => {
    setTimeout(initEditors, 100);
});

document.addEventListener('render-code-editor', () => {
    setTimeout(initEditors, 100);
});
