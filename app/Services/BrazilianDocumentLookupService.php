<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

final class BrazilianDocumentLookupService
{
    /**
     * @return array<string, mixed>
     */
    public function lookupCnpj(string $document): array
    {
        $cnpj = $this->digits($document);

        if (strlen($cnpj) !== 14) {
            throw new RuntimeException('Informe um CNPJ válido com 14 dígitos.');
        }

        try {
            $response = Http::acceptJson()
                ->timeout(10)
                ->retry(2, 250)
                ->get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");

            if ($response->notFound()) {
                throw new RuntimeException('CNPJ não encontrado.');
            }

            $response->throw();

            $data = $response->json();

            return [
                'document' => $this->formatCnpj($cnpj),
                'legal_name' => $data['razao_social'] ?? null,
                'name' => $data['nome_fantasia'] ?: ($data['razao_social'] ?? null),
                'email' => $data['email'] ?? null,
                'phone' => $this->firstPhone($data),
                'state_registration' => $data['inscricao_estadual'] ?? null,
                'zip_code' => $this->formatCep((string) ($data['cep'] ?? '')),
                'street' => $this->joinStreet(
                    $data['descricao_tipo_de_logradouro'] ?? null,
                    $data['logradouro'] ?? null,
                ),
                'number' => $data['numero'] ?? null,
                'complement' => $data['complemento'] ?? null,
                'district' => $data['bairro'] ?? null,
                'city' => $data['municipio'] ?? null,
                'state' => isset($data['uf']) ? mb_strtoupper((string) $data['uf']) : null,
                'external_data' => $data,
            ];
        } catch (ConnectionException) {
            throw new RuntimeException('Não foi possível conectar ao serviço de consulta de CNPJ.');
        } catch (RuntimeException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new RuntimeException('Não foi possível consultar o CNPJ neste momento.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function lookupCep(string $zipCode): array
    {
        $cep = $this->digits($zipCode);

        if (strlen($cep) !== 8) {
            throw new RuntimeException('Informe um CEP válido com 8 dígitos.');
        }

        try {
            $response = Http::acceptJson()
                ->timeout(8)
                ->retry(2, 200)
                ->get("https://brasilapi.com.br/api/cep/v2/{$cep}");

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'zip_code' => $this->formatCep($cep),
                    'street' => $data['street'] ?? null,
                    'district' => $data['neighborhood'] ?? null,
                    'city' => $data['city'] ?? null,
                    'state' => isset($data['state']) ? mb_strtoupper((string) $data['state']) : null,
                ];
            }

            $fallback = Http::acceptJson()
                ->timeout(8)
                ->retry(2, 200)
                ->get("https://viacep.com.br/ws/{$cep}/json/");

            $fallback->throw();
            $data = $fallback->json();

            if (($data['erro'] ?? false) === true) {
                throw new RuntimeException('CEP não encontrado.');
            }

            return [
                'zip_code' => $this->formatCep($cep),
                'street' => $data['logradouro'] ?? null,
                'district' => $data['bairro'] ?? null,
                'city' => $data['localidade'] ?? null,
                'state' => isset($data['uf']) ? mb_strtoupper((string) $data['uf']) : null,
            ];
        } catch (ConnectionException) {
            throw new RuntimeException('Não foi possível conectar ao serviço de consulta de CEP.');
        } catch (RuntimeException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new RuntimeException('Não foi possível consultar o CEP neste momento.');
        }
    }

    private function digits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    private function formatCnpj(string $value): string
    {
        $digits = $this->digits($value);

        if (strlen($digits) !== 14) {
            return $value;
        }

        return preg_replace(
            '/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/',
            '$1.$2.$3/$4-$5',
            $digits,
        ) ?? $value;
    }

    private function formatCep(string $value): ?string
    {
        $digits = $this->digits($value);

        if (strlen($digits) !== 8) {
            return $value !== '' ? $value : null;
        }

        return substr($digits, 0, 5) . '-' . substr($digits, 5);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function firstPhone(array $data): ?string
    {
        $ddd1 = (string) ($data['ddd_telefone_1'] ?? '');
        $phone1 = (string) ($data['telefone_1'] ?? '');

        if ($phone1 !== '') {
            return trim("({$ddd1}) {$phone1}");
        }

        $ddd2 = (string) ($data['ddd_telefone_2'] ?? '');
        $phone2 = (string) ($data['telefone_2'] ?? '');

        return $phone2 !== '' ? trim("({$ddd2}) {$phone2}") : null;
    }

    private function joinStreet(?string $type, ?string $street): ?string
    {
        $value = trim(implode(' ', array_filter([$type, $street])));

        return $value !== '' ? $value : null;
    }
}
