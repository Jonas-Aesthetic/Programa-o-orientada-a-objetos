<?php
header('Content-Type: application/json');
$path = '../data/compromissos.json';
$banco = json_decode(file_get_contents($path), true);

$metodo = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

// ROTEAMENTO DIRETO
switch ($metodo) {
    case 'GET':
        // Se tem ID, filtra. Se não, mostra todos.
        $res = $id ? array_filter($banco['compromissos'], fn($c) => $c['id'] == $id) : $banco['compromissos'];
        echo json_encode(array_values($res));
        break;

    case 'POST':
        $novo = json_decode(file_get_contents('php://input'), true);
        $novo['id'] = uniqid(); // Gera um ID único simples
        $banco['compromissos'][] = $novo;
        
        file_put_contents($path, json_encode($banco));
        echo json_encode($novo);
        break;

    case 'DELETE':
        $banco['compromissos'] = array_filter($banco['compromissos'], fn($c) => $c['id'] != $id);
        file_put_contents($path, json_encode($banco));
        echo json_encode(['mensagem' => 'Removido']);
        break;
}
