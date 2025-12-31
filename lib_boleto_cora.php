<?php
/**
 * Biblioteca para geração de boletos via CORA
 * Documentação: https://docs.cora.com.br
 */

require_once 'config.php';

class BoletoCora {
    private $apiKey;
    private $apiSecret;
    private $baseUrl = 'https://api.cora.com.br/v1';
    
    public function __construct() {
        $conn = getConnection();
        $stmt = $conn->query("SELECT api_key, api_secret, ativo FROM integracoes WHERE tipo = 'cora'");
        $config = $stmt->fetch();
        
        if (!$config || !$config['ativo']) {
            throw new Exception('Integração CORA não está ativa ou configurada.');
        }
        
        $this->apiKey = $config['api_key'];
        $this->apiSecret = $config['api_secret'];
    }
    
    /**
     * Gerar boleto via CORA
     * 
     * @param array $dados Dados do boleto
     * @return array Resposta da API
     */
    public function gerarBoleto($dados) {
        // Validar dados obrigatórios
        $required = ['valor', 'data_vencimento', 'cliente_nome', 'cliente_documento'];
        foreach ($required as $field) {
            if (empty($dados[$field])) {
                throw new Exception("Campo obrigatório ausente: $field");
            }
        }
        
        // Preparar dados para a API CORA
        $boletoData = [
            'amount' => (int)($dados['valor'] * 100), // Converter para centavos
            'due_date' => $dados['data_vencimento'],
            'payer' => [
                'name' => $dados['cliente_nome'],
                'document' => preg_replace('/[^0-9]/', '', $dados['cliente_documento']),
                'email' => $dados['cliente_email'] ?? null,
                'phone' => $dados['cliente_telefone'] ?? null,
                'address' => [
                    'street' => $dados['cliente_logradouro'] ?? '',
                    'number' => $dados['cliente_numero'] ?? '',
                    'complement' => $dados['cliente_complemento'] ?? '',
                    'neighborhood' => $dados['cliente_bairro'] ?? '',
                    'city' => $dados['cliente_cidade'] ?? '',
                    'state' => $dados['cliente_estado'] ?? '',
                    'zip_code' => preg_replace('/[^0-9]/', '', $dados['cliente_cep'] ?? '')
                ]
            ],
            'description' => $dados['descricao'] ?? 'Pagamento via boleto',
            'metadata' => [
                'conta_receber_id' => $dados['conta_receber_id'] ?? ''
            ]
        ];
        
        // Fazer requisição para API CORA
        $response = $this->makeRequest('POST', '/boletos', $boletoData);
        
        if (isset($response['error'])) {
            throw new Exception('Erro CORA: ' . ($response['error']['message'] ?? 'Erro desconhecido'));
        }
        
        // Extrair informações do boleto
        $resultado = [
            'sucesso' => true,
            'boleto_id' => $response['id'] ?? null,
            'status' => $this->mapearStatus($response['status'] ?? 'pending'),
            'valor' => $dados['valor'],
            'data_vencimento' => $dados['data_vencimento'],
            'url_boleto' => $response['url'] ?? null,
            'url_pdf' => $response['pdf_url'] ?? null,
            'codigo_barras' => $response['barcode'] ?? null,
            'linha_digitavel' => $response['digitable_line'] ?? null,
            'resposta_completa' => json_encode($response)
        ];
        
        return $resultado;
    }
    
    /**
     * Consultar status de um boleto
     * 
     * @param string $boletoId ID do boleto no CORA
     * @return array Status do boleto
     */
    public function consultarBoleto($boletoId) {
        $response = $this->makeRequest('GET', "/boletos/$boletoId");
        
        if (isset($response['error'])) {
            throw new Exception('Erro ao consultar boleto: ' . ($response['error']['message'] ?? 'Erro desconhecido'));
        }
        
        return [
            'boleto_id' => $response['id'],
            'status' => $this->mapearStatus($response['status']),
            'valor' => ($response['amount'] ?? 0) / 100,
            'codigo_barras' => $response['barcode'] ?? null,
            'linha_digitavel' => $response['digitable_line'] ?? null,
            'resposta_completa' => json_encode($response)
        ];
    }
    
    /**
     * Cancelar um boleto
     * 
     * @param string $boletoId ID do boleto no CORA
     * @return array Resultado do cancelamento
     */
    public function cancelarBoleto($boletoId) {
        $response = $this->makeRequest('DELETE', "/boletos/$boletoId");
        
        if (isset($response['error'])) {
            throw new Exception('Erro ao cancelar boleto: ' . ($response['error']['message'] ?? 'Erro desconhecido'));
        }
        
        return [
            'sucesso' => true,
            'boleto_id' => $boletoId,
            'status' => 'cancelado'
        ];
    }
    
    /**
     * Fazer requisição para API CORA
     */
    private function makeRequest($method, $endpoint, $data = []) {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'X-Api-Secret: ' . $this->apiSecret,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        // Se não conseguir decodificar ou houver erro HTTP, retornar erro
        if ($httpCode >= 400 || !$result) {
            return [
                'error' => [
                    'message' => $result['message'] ?? 'Erro na comunicação com CORA',
                    'code' => $httpCode
                ]
            ];
        }
        
        return $result;
    }
    
    /**
     * Mapear status do CORA para status do sistema
     */
    private function mapearStatus($statusCora) {
        $mapeamento = [
            'pending' => 'pendente',
            'paid' => 'pago',
            'canceled' => 'cancelado',
            'expired' => 'vencido',
            'overdue' => 'vencido'
        ];
        
        return $mapeamento[$statusCora] ?? 'pendente';
    }
}
?>
