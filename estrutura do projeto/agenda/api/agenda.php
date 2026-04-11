<?php

// RA: 202510196 
// feito por Jonas Berlanda

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
 
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
 
class Compromisso {
 
    private string $id;
    private string $titulo;
    private string $descricao;
    private string $data;
    private string $horario;
    private string $categoria;
    private string $created_at;
 
    public function __construct(array $dados, string $id = '') {
        $this->id         = $id ?: uniqid();
        $this->titulo     = $dados['titulo']     ?? '';
        $this->descricao  = $dados['descricao']  ?? '';
        $this->data       = $dados['data']       ?? '';
        $this->horario    = $dados['horario']     ?? '';
        $this->categoria  = $dados['categoria']  ?? '';
        $this->created_at = $dados['created_at'] ?? date('Y-m-d\TH:i:s');
    }
 
    // Getters usados 
    public function getId():       string { return $this->id; }
    public function getTitulo():   string { return $this->titulo; }
    public function getCreatedAt():string { return $this->created_at; }
 
    // Setters usados para validação
    public function setTitulo(string $v):    void { $this->titulo    = $v; }
    public function setDescricao(string $v): void { $this->descricao = $v; }
    public function setData(string $v):      void { $this->data      = $v; }
    public function setHorario(string $v):   void { $this->horario   = $v; }
    public function setCategoria(string $v): void { $this->categoria = $v; }
 
 
    public function validar(): array {
        $erros = [];
        if (empty($this->titulo))  $erros[] = 'Título obrigatório.';
        if (empty($this->data))    $erros[] = 'Data obrigatória.';
        if (empty($this->horario)) $erros[] = 'Horário obrigatório.';
        return $erros;
    }
 
    // Converte objeto para array e salva tudo no meu arquivo compromissos.json
    public function toArray(): array {
        return [
            'id'         => $this->id,
            'titulo'     => $this->titulo,
            'descricao'  => $this->descricao,
            'data'       => $this->data,
            'horario'    => $this->horario,
            'categoria'  => $this->categoria,
            'created_at' => $this->created_at,
        ];
    }
}
 

class Banco {
 
    private string $arquivo;
 
    public function __construct(string $arquivo) {
        $this->arquivo = $arquivo;
    }
 
    public function ler(): array {
        if (!file_exists($this->arquivo)) return ['compromissos' => []];
        return json_decode(file_get_contents($this->arquivo), true) ?? ['compromissos' => []];
    }
 
    public function salvar(array $dados): void {
        file_put_contents($this->arquivo, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
 
class Agendamento {
 
    private Banco $banco;
 
    public function __construct(Banco $banco) {
        $this->banco = $banco;
    }
 
    public function processar(): void {
        $metodo = $_SERVER['REQUEST_METHOD'];
        $id     = $_GET['id'] ?? null;
 
        match ($metodo) {
            'GET'    => $this->listar($id),
            'POST'   => $this->criar(),
            'PUT'    => $this->atualizar($id),
            'DELETE' => $this->remover($id),
            default  => $this->responder(405, ['erro' => 'Método não permitido.'])
        };
    }
 
    // GET — lista todos ou busca por ID especifico 
    private function listar(?string $id): void {
        $dados = $this->banco->ler();
 
        if ($id) {
            $item = current(array_filter($dados['compromissos'], fn($c) => $c['id'] == $id));
            $item
                ? $this->responder(200, (new Compromisso($item, $item['id']))->toArray())
                : $this->responder(404, ['erro' => 'Não encontrado.']);
            return;
        }
 
        $lista = array_map(fn($c) => (new Compromisso($c, $c['id']))->toArray(), $dados['compromissos']);
        $this->responder(200, array_values($lista));
    }
 
    // POST — cria novo compromisso na agenda
    private function criar(): void {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $compromisso = new Compromisso($input);
 
        $erros = $compromisso->validar();
        if ($erros) { $this->responder(422, ['erros' => $erros]); return; }
 
        $dados = $this->banco->ler();
        $dados['compromissos'][] = $compromisso->toArray();
        $this->banco->salvar($dados);
 
        $this->responder(201, $compromisso->toArray());
    }
 
    // PUT — atualiza a agenda de compromisso existente
    private function atualizar(?string $id): void {
        if (!$id) { $this->responder(400, ['erro' => 'ID não informado.']); return; }
 
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $dados = $this->banco->ler();
 
        foreach ($dados['compromissos'] as &$item) {
            if ($item['id'] == $id) {
                $compromisso = new Compromisso($item, $item['id']);
 
                if (isset($input['titulo']))    $compromisso->setTitulo($input['titulo']);
                if (isset($input['descricao'])) $compromisso->setDescricao($input['descricao']);
                if (isset($input['data']))      $compromisso->setData($input['data']);
                if (isset($input['horario']))   $compromisso->setHorario($input['horario']);
                if (isset($input['categoria'])) $compromisso->setCategoria($input['categoria']);
 
                $erros = $compromisso->validar();
                if ($erros) { $this->responder(422, ['erros' => $erros]); return; }
 
                $item = $compromisso->toArray();
                $this->banco->salvar($dados);
                $this->responder(200, $item);
                return;
            }
        }
 
        $this->responder(404, ['erro' => 'Não encontrado.']);
    }
 
    // DELETE — remove compromissos de acordo com id escolhido
    private function remover(?string $id): void {
        if (!$id) { $this->responder(400, ['erro' => 'ID não informado.']); return; }
 
        $dados    = $this->banco->ler();
        $antes    = count($dados['compromissos']);
        $dados['compromissos'] = array_values(
            array_filter($dados['compromissos'], fn($c) => $c['id'] != $id)
        );
 
        if (count($dados['compromissos']) === $antes) {
            $this->responder(404, ['erro' => 'Não encontrado.']); return;
        }
 
        $this->banco->salvar($dados);
        $this->responder(200, ['mensagem' => 'Compromisso removido com sucesso.']);
    }
 
    // Envia resposta JSON
    private function responder(int $status, array $dados): void {
        http_response_code($status);
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    }
}
 
$banco      = new Banco(__DIR__ . '/../data/compromissos.json');
$agendamento = new Agendamento($banco);
$agendamento->processar();
