<?php

namespace LoggiHandler;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LoggiHandler extends AbstractProcessingHandler
{
    private HttpClientInterface $httpClient;
    private string $endpoint;

    /**
     * Constructeur du LoggiHandler.
     *
     * @param HttpClientInterface $httpClient L'instance du client HTTP Symfony.
     * @param string $endpoint L'endpoint complet de l'API Loggi, par ex. "https://api.loggi.d.auc.ovh/projects/1/logs"
     * @param int $level Niveau minimal du log à traiter (par défaut DEBUG).
     * @param bool $bubble Indique si le message doit continuer à être passé aux autres handlers.
     */
    public function __construct(HttpClientInterface $httpClient, string $endpoint, int $level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->httpClient = $httpClient;
        $this->endpoint   = $endpoint;
    }

    /**
     * Traitement du log et envoi vers l'API Loggi.
     *
     * @param array $record Tableau de données du log.
     */
    protected function write(array $record): void
    {
        // Préparer les données du log
        $logData = [
            'message'    => $record['message'],
            'level'      => $record['level_name'],
            'context'    => $record['context'],
            'created_at' => $record['datetime']->format('Y-m-d H:i:s'),
        ];

        try {
            // Envoi d'une requête POST à l'API Loggi
            $this->httpClient->request('POST', $this->endpoint, [
                'json' => $logData,
            ]);
        } catch (\Exception $e) {
            // En cas d'erreur, on peut logger l'erreur dans un canal interne ou simplement l'ignorer
            error_log('LoggiHandler: '.$e->getMessage());
        }
    }
}
