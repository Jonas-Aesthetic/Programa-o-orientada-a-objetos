<?php
/**
 * Sistema de Agenda — API Backend (PHP)
 * Arquivo: api/agenda.php
 * 
 * Rotas disponíveis:
 *   GET    /api/agenda.php            → lista todos os compromissos
 *   GET    /api/agenda.php?id=X       → retorna compromisso específico
 *   POST   /api/agenda.php            → cria novo compromisso
 *   PUT    /api/agenda.php?id=X       → edita compromisso existente
 *   DELETE /api/agenda.php?id=X       → remove compromisso
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ─── Configurações ────────────────────────────────────────────────────────────
define('DB_PATH', __DIR__ . '/../data/compromissos.json');

// ─── Helpers ─────────────────────────────────────────────────────────────────
function lerBanco(): array {
    if (!file_exists(DB_PATH)) {
        return ['compromissos' => []];
    }
    $conteudo = file_get_contents(DB_PATH);
    return json_decode($conteudo, true) ?? ['compromissos' => []];
}

function salvarBanco(array $dados): bool {
    return file_put_contents(
        DB_PATH,
        json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    ) !== false;
}

function gerarId(array $compromissos): string {
    if (empty($compromissos)) return '1';
    $ids = array_map(fn($c) => (int) $c['id'], $compromissos);
    return (string)(max($ids) + 1);
}

function validarCampos(array $dados): array {
    $erros = [];
    if (empty(trim($dados['titulo'] ?? ''))) {
        $erros[] = 'O campo "título" é obrigatório.';
    }
    if (empty(trim($dados['data'] ?? ''))) {
        $erros[] = 'O campo "data" é obrigatório.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dados['data'])) {
        $erros[] = 'O campo "data" deve estar no formato AAAA-MM-DD.';
    }
    if (empty(trim($dados['horario'] ?? ''))) {
        $erros[] = 'O campo "horário" é obrigatório.';
    }
    return $erros;
}

function responder(int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit();
}

// ─── Roteador ─────────────────────────────────────────────────────────────────
$metodo = $_SERVER['REQUEST_METHOD'];
$id     = $_GET['id'] ?? null;
$banco  = lerBanco();

switch ($metodo) {

    // ── GET ──────────────────────────────────────────────────────────────────
    case 'GET':
        if ($id !== null) {
            $encontrado = null;
            foreach ($banco['compromissos'] as $c) {
                if ($c['id'] === $id) { $encontrado = $c; break; }
            }
            if ($encontrado) {
                responder(200, ['sucesso' => true, 'compromisso' => $encontrado]);
            } else {
                responder(404, ['sucesso' => false, 'mensagem' => 'Compromisso não encontrado.']);
            }
        }
        // Ordenar por data + horário
        $lista = $banco['compromissos'];
        usort($lista, fn($a, $b) => strcmp($a['data'] . $a['horario'], $b['data'] . $b['horario']));
        responder(200, ['sucesso' => true, 'compromissos' => $lista, 'total' => count($lista)]);
        break;

    // ── POST ─────────────────────────────────────────────────────────────────
    case 'POST':
        $dados = json_decode(file_get_contents('php://input'), true) ?? [];
        $erros = validarCampos($dados);
        if (!empty($erros)) {
            responder(422, ['sucesso' => false, 'erros' => $erros]);
        }
        $novo = [
            'id'         => gerarId($banco['compromissos']),
            'titulo'     => trim($dados['titulo']),
            'descricao'  => trim($dados['descricao'] ?? ''),
            'data'       => $dados['data'],
            'horario'    => $dados['horario'],
            'categoria'  => $dados['categoria'] ?? 'outro',
            'created_at' => date('c'),
        ];
        $banco['compromissos'][] = $novo;
        salvarBanco($banco);
        responder(201, ['sucesso' => true, 'mensagem' => 'Compromisso criado com sucesso!', 'compromisso' => $novo]);
        break;

    // ── PUT ──────────────────────────────────────────────────────────────────
    case 'PUT':
        if (!$id) {
            responder(400, ['sucesso' => false, 'mensagem' => 'ID não informado.']);
        }
        $dados   = json_decode(file_get_contents('php://input'), true) ?? [];
        $erros   = validarCampos($dados);
        if (!empty($erros)) {
            responder(422, ['sucesso' => false, 'erros' => $erros]);
        }
        $indice = null;
        foreach ($banco['compromissos'] as $i => $c) {
            if ($c['id'] === $id) { $indice = $i; break; }
        }
        if ($indice === null) {
            responder(404, ['sucesso' => false, 'mensagem' => 'Compromisso não encontrado.']);
        }
        $banco['compromissos'][$indice] = array_merge($banco['compromissos'][$indice], [
            'titulo'    => trim($dados['titulo']),
            'descricao' => trim($dados['descricao'] ?? ''),
            'data'      => $dados['data'],
            'horario'   => $dados['horario'],
            'categoria' => $dados['categoria'] ?? 'outro',
        ]);
        salvarBanco($banco);
        responder(200, [
            'sucesso'      => true,
            'mensagem'     => 'Compromisso atualizado com sucesso!',
            'compromisso'  => $banco['compromissos'][$indice],
        ]);
        break;

    // ── DELETE ───────────────────────────────────────────────────────────────
    case 'DELETE':
        if (!$id) {
            responder(400, ['sucesso' => false, 'mensagem' => 'ID não informado.']);
        }
        $indice = null;
        foreach ($banco['compromissos'] as $i => $c) {
            if ($c['id'] === $id) { $indice = $i; break; }
        }
        if ($indice === null) {
            responder(404, ['sucesso' => false, 'mensagem' => 'Compromisso não encontrado.']);
        }
        array_splice($banco['compromissos'], $indice, 1);
        salvarBanco($banco);
        responder(200, ['sucesso' => true, 'mensagem' => 'Compromisso removido com sucesso!']);
        break;

    default:
        responder(405, ['sucesso' => false, 'mensagem' => 'Método não permitido.']);
}
