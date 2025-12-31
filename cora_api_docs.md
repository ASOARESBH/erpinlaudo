# Documentação API CORA - Resumo

## Credenciais

### Integração Direta (Nosso Caso)
- **client-id**: int-6f2u3vpjglGsZ8nev37Wm7
- **Certificado e Private Key**: cert_key_cora_production_2025_12_14.zip
- **Autenticação**: mTLS (Mutual TLS) usando certificado

### URLs Base

**Ambiente de Teste (Stage)**:
```
https://matls-clients.api.stage.cora.com.br/
```

**Ambiente de Produção**:
```
https://matls-clients.api.cora.com.br/
```

## Emissão de Boleto Registrado V2

### Endpoint
```
POST /v2/invoices/
```

### Headers Obrigatórios
- `Content-Type: application/json`
- `Accept: application/json`
- `Idempotency-Key: {UUID único}` - Para evitar duplicação
- `Authorization: Bearer {token}` - Obtido via OAuth2

### Estrutura do Corpo da Requisição

```json
{
  "code": "string",  // ID único do boleto no sistema do cliente
  "customer": {
    "name": "string",  // Nome do cliente
    "email": "string",  // E-mail do cliente
    "document": {
      "identity": "string",  // CPF ou CNPJ (apenas números)
      "type": "CPF" | "CNPJ"  // Tipo de documento
    },
    "address": {
      "street": "string",
      "number": "string",
      "district": "string",  // Bairro
      "city": "string",
      "state": "string",  // UF (2 letras)
      "complement": "string",  // Opcional
      "country": "BRA",  // Código do país
      "zip_code": "string"  // CEP (apenas números)
    }
  },
  "services": [
    {
      "name": "string",  // Nome do serviço
      "description": "string",  // Descrição
      "amount": 0  // Valor em centavos
    }
  ],
  "payment_terms": {
    "due_date": "YYYY-MM-DD",  // Data de vencimento
    "fine": {  // Multa (opcional)
      "date": "YYYY-MM-DD",  // Data de início da multa
      "amount": 0,  // Valor fixo em centavos (opcional)
      "rate": 0.0  // Taxa percentual (opcional)
    },
    "interest": {  // Juros (opcional)
      "date": "YYYY-MM-DD",  // Data de início dos juros
      "amount": 0,  // Valor fixo por dia em centavos (opcional)
      "rate": 0.0  // Taxa percentual ao mês (opcional)
    },
    "discount": {  // Desconto (opcional)
      "date": "YYYY-MM-DD",  // Data limite para desconto
      "amount": 0,  // Valor fixo em centavos (opcional)
      "rate": 0.0  // Taxa percentual (opcional)
    }
  },
  "pix": {
    // Objeto Pix obrigatório (detalhes a verificar)
  }
}
```

### Resposta de Sucesso (201 Created)

```json
{
  "id": "string",  // ID do boleto na CORA
  "status": "string",  // Status do boleto
  "created_at": "YYYY-MM-DDTHH:MM:SS",  // Data de criação
  "total_amount": 0,  // Valor total em centavos
  "total_paid": 0,  // Valor pago em centavos
  "occurrence_date": "YYYY-MM-DDTHH:MM:SS",
  "code": "string",  // Código único enviado
  "digitable_line": "string",  // Linha digitável do boleto
  "barcode": "string",  // Código de barras
  "pdf_url": "string"  // URL do PDF do boleto
}
```

## Autenticação OAuth2

### Fluxo de Autenticação
1. Usar certificado mTLS para autenticar
2. Obter token de acesso
3. Usar token no header Authorization

### Endpoint de Token (a confirmar)
```
POST /oauth/token
```

## Consulta de Boletos

### Endpoint
```
GET /v2/invoices/
```

### Parâmetros de Paginação
- `page`: Número da página (inicia em 0)
- `size`: Quantidade de itens por página (padrão: 50)

## Consulta de Detalhes do Boleto

### Endpoint
```
GET /v2/invoices/{invoice_id}
```

## Cancelamento de Boleto

### Endpoint
```
DELETE /v2/invoices/{invoice_id}
```

## Códigos de Erro

### 2xx - Sucesso
- 200: OK
- 201: Created

### 4xx - Erro do Cliente
- 400: Bad Request (invalid_request)
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found

### 5xx - Erro do Servidor
- 500: Internal Server Error (server_error)

### Formato de Erro
```json
{
  "code": "invalid_request",
  "message": "Request has invalid parameters",
  "errors": [
    {
      "id": "campo.subcampo",
      "message": "mensagem de erro"
    }
  ]
}
```

## Formato de Datas

### Requisições
- Formato: `YYYY-MM-DD`
- Exemplo: `2021-10-10`

### Respostas
- Formato ISO: `YYYY-MM-DDTHH:MM:SS`
- Exemplo: `2021-06-23T13:58:21`

## Idempotência

- Usar header `Idempotency-Key` com UUID único
- Evita duplicação de boletos em caso de falha de conexão
- Formato: UUID v4
- Exemplo: `f08dad6b-96bc-4e5e-ad82-acb34a8749e4`

## Observações Importantes

1. **Integração Direta** usa mTLS com certificado
2. **Parceria Cora** usa client-id e client-secret (não é nosso caso)
3. Valores monetários sempre em **centavos**
4. CPF/CNPJ sempre **apenas números** (sem formatação)
5. CEP sempre **apenas números** (sem hífen)
6. Estado sempre **2 letras** (UF)
7. País sempre **3 letras** (BRA)
