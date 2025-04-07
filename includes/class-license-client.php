<?php
/**
 * 🎟️ Klient API do Centrum Licencji
 * Do wykorzystania we wtyczkach WordPress – umożliwia aktywację, weryfikację i dezaktywację licencji
 */
class CentrumLicencji_Client {

    protected $api_url = 'https://raf-comp.net.pl/wp-json/centrum-licencji/v1';
    protected $license_key;
    protected $domain_url;
    protected $api_key;

    /**
     * 🔐 Inicjalizacja klienta
     *
     * @param string $license_key Klucz licencyjny
     * @param string $domain_url  Adres domeny (np. site_url())
     * @param string $api_key     Klucz API z panelu Centrum Licencji
     */
    public function __construct($license_key, $domain_url, $api_key) {
        $this->license_key = $license_key;
        $this->domain_url  = $domain_url;
        $this->api_key     = $api_key;
    }

    /**
     * 🔄 Wysyła żądanie do API
     *
     * @param string $endpoint Nazwa endpointu ('activate', 'verify', 'deactivate', ...)
     * @param array  $data     Dodatkowe dane (opcjonalne)
     * @return array Odpowiedź z API ['status' => ..., 'message' => ...]
     */
    protected function request($endpoint, $data = []) {
        $url = "{$this->api_url}/{$endpoint}";

        $args = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
            'body'    => wp_json_encode(array_merge([
                'license_key' => $this->license_key,
                'domain_url'  => $this->domain_url,
                'api_key'     => $this->api_key,
            ], $data)),
            'timeout' => 10,
        ];

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            return ['status' => 'error', 'message' => $response->get_error_message()];
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code !== 200 || empty($body)) {
            return ['status' => 'error', 'message' => 'Nieprawidłowa odpowiedź API.'];
        }

        return $body;
    }

    /**
     * 📥 Aktywuje licencję (endpoint: /activate)
     *
     * @return array
     */
    public function activate() {
        return $this->request('activate');
    }

    /**
     * 🔎 Weryfikuje licencję (endpoint: /verify)
     *
     * @return array
     */
    public function verify() {
        return $this->request('verify');
    }

    /**
     * ❌ Dezaktywuje licencję (endpoint: /deactivate)
     *
     * @return array
     */
    public function deactivate() {
        return $this->request('deactivate');
    }
}
