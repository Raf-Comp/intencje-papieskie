<?php
/**
 * ℹ️ Strona informacyjna o działaniu wtyczki Intencje Papieskie
 */

if (!defined('ABSPATH')) exit;

if (!current_user_can('manage_options')) {
    wp_die('Brak dostępu');
}

/**
 * 📘 Klasa IP_Info_Page – wyświetla opis działania shortcode'ów
 */
class IP_Info_Page {

    /**
     * 🖥️ Renderuje zawartość strony z instrukcją
     */
    public function render() {
        ?>
        <div class="wrap">
            <h1>ℹ️ O wtyczce: Intencje Papieskie</h1>
            <p>Ta wtyczka służy do importowania i wyświetlania miesięcznych intencji papieskich. Posiada system archiwizacji oraz możliwość pobierania intencji w formacie PDF.</p>

            <h2>📜 Dostępne shortcody</h2>

            <h3>🔹 [intencje_papieskie]</h3>
            <p><strong>Opis:</strong><br>
            Wyświetla aktualne intencje papieskie na dany rok (domyślnie bieżący).</p>

            <p><strong>Funkcje:</strong></p>
            <ul>
                <li>Wyświetlanie od stycznia do grudnia w 2 kolumnach</li>
                <li>Automatyczne przenoszenie zakończonych miesięcy do archiwum</li>
            </ul>
            <p><strong>Przyciski:</strong></p>
            <ul>
                <li>Zobacz archiwum intencji papieskich</li>
                <li>Pobierz PDF intencji na [rok] (jeśli rok dostępny)</li>
            </ul>

            <h3>🔹 [intencje_papieskie_archiwum]</h3>
            <p><strong>Opis:</strong><br>
            Wyświetla intencje z lat wcześniejszych (automatycznie oznaczone jako archiwum).</p>

            <p><strong>Funkcje:</strong></p>
            <ul>
                <li>Lista rozwijana do wyboru roku</li>
                <li>Intencje wyświetlane miesiącami (pełny rok)</li>
                <li>Przycisk powrotu do strony głównej z intencjami</li>
            </ul>
        </div>
        <?php
    }
}

$page = new IP_Info_Page();
$page->render();
