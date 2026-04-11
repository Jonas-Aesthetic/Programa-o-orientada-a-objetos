# 📅 Sistema de Agendamento em PHP

O desenvolvimento deste sistema de agenda em PHP começa com o planejamento das funcionalidades, como cadastrar, visualizar, editar e excluir compromissos. O banco de dados é representado por um arquivo JSON que armazena informações como título, descrição, data e horário dos eventos. A interface do sistema é desenvolvida com HTML e CSS, permitindo que o usuário interaja com a agenda de forma simples e intuitiva. O PHP é responsável por processar as requisições e conectar o frontend ao banco de dados JSON. Assim, o sistema consegue salvar e mostrar os compromissos cadastrados. Por fim, são realizados testes para garantir que tudo funcione corretamente antes de disponibilizar o sistema.

---

## 📁 Estrutura de Arquivos

```
/
├── api/
│   └── agenda.php          ← API PHP com as 3 classes
└── data/
    └── compromissos.json   ← Banco de dados JSON
```

---

## 🧱 Classes PHP (POO)

### `Compromisso`
Representa um compromisso da agenda. Possui atributos privados, getters, setters, validação e conversão para array.

| Atributo | Tipo | Descrição |
|---|---|---|
| `$id` | string | Identificador único |
| `$titulo` | string | Nome do compromisso |
| `$descricao` | string | Detalhes do evento |
| `$data` | string | Data no formato AAAA-MM-DD |
| `$horario` | string | Horário no formato HH:MM |
| `$categoria` | string | trabalho, saude ou pessoal |
| `$created_at` | string | Data e hora de criação |

**Métodos:**
- `validar()` — verifica se os campos obrigatórios estão preenchidos
- `toArray()` — converte o objeto para array (para salvar no JSON)

---

### `Banco`
Responsável por ler e salvar o arquivo `compromissos.json`.

| Método | Descrição |
|---|---|
| `ler()` | Lê e decodifica o arquivo JSON |
| `salvar()` | Codifica e grava os dados no arquivo |

---

### `Agendamento`
Processa as requisições HTTP e executa o CRUD. Recebe o objeto `Banco` pelo construtor.

| Método | Descrição |
|---|---|
| `processar()` | Identifica o método HTTP e direciona a ação |
| `listar()` | Retorna todos ou um compromisso por ID |
| `criar()` | Cadastra um novo compromisso |
| `atualizar()` | Edita um compromisso existente |
| `remover()` | Exclui um compromisso |

---

## 🔌 Rotas da API

| Método | URL | Ação |
|---|---|---|
| `GET` | `api/agenda.php` | Lista todos os compromissos |
| `GET` | `api/agenda.php?id=1` | Busca um compromisso por ID |
| `POST` | `api/agenda.php` | Cadastra novo compromisso |
| `PUT` | `api/agenda.php?id=1` | Edita compromisso existente |
| `DELETE` | `api/agenda.php?id=1` | Exclui um compromisso |

---

## 📦 Exemplo de Compromisso (JSON)

```json
{
  "id": "1",
  "titulo": "Reunião de planejamento",
  "descricao": "Reunião semanal com a equipe para alinhar metas e tarefas.",
  "data": "2026-04-15",
  "horario": "09:00",
  "categoria": "trabalho",
  "created_at": "2026-04-09T10:00:00"
}
```

---

## 🚀 Como Usar

**Requisitos:**
- PHP 8.0 ou superior
- Servidor Apache ou Nginx com suporte a PHP

**Passos:**
1. Coloque os arquivos em uma pasta no servidor (ex: `htdocs/agenda`)
2. Garanta que a pasta `data/` tem permissão de escrita:
```bash
chmod 664 data/compromissos.json
```
3. Acesse via navegador ou faça requisições à API:
```
http://localhost/agenda/api/agenda.php
```

---

## ✅ Funcionalidades

- [x] Cadastrar compromisso
- [x] Visualizar todos os compromissos
- [x] Buscar compromisso por ID
- [x] Editar compromisso existente
- [x] Excluir compromisso
- [x] Validação dos campos obrigatórios
- [x] Respostas HTTP com status codes corretos
