<?php
header('Content-Type: application/json');

class Agendamento {
    private $path = '../data/compromissos.json';

    // Busca os dados do arquivo
    private function lerBanco() {
        return json_decode(file_get_contents($this->path), true) ?? ['compromissos' => []];
    }

    // Executa as ações conforme o método HTTP
    public function processar() {
        $metodo = $_SERVER['REQUEST_METHOD'];
        $id = $_GET['id'] ?? null;
        $banco = $this->lerBanco();

        switch ($metodo) {
            case 'GET':
                $res = $id ? array_filter($banco['compromissos'], fn($c) => $c['id'] == $id) : $banco['compromissos'];
                echo json_encode(array_values($res));
                break;

            case 'POST':
                $novo = json_decode(file_get_contents('php://input'), true);
                $novo['id'] = uniqid();
                $banco['compromissos'][] = $novo;
                file_put_contents($this->path, json_encode($banco));
                echo json_encode($novo);
                break;

            case 'DELETE':
                $banco['compromissos'] = array_filter($banco['compromissos'], fn($c) => $c['id'] != $id);
                file_put_contents($this->path, json_encode($banco));
                echo json_encode(['mensagem' => 'Removido']);
                break;
        }
    }
}

// EXECUÇÃO SIMPLES
$api = new Agendamento();
$api->processar();
