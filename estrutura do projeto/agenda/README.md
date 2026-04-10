# 📅 Sistema de Agenda em PHP + JSON

Sistema completo de agendamento de compromissos com frontend moderno e backend PHP usando JSON como banco de dados.

---

## 📁 Estrutura do Projeto

```
agenda/
├── index.html           ← Frontend (HTML + CSS + JavaScript)
├── .htaccess            ← Configuração Apache
├── api/
│   └── agenda.php       ← API RESTful em PHP
└── data/
    └── compromissos.json ← Banco de dados JSON
```

---

## 🚀 Como rodar

### Requisitos
- PHP 8.0+
- Apache ou Nginx com mod_rewrite habilitado
- Permissão de escrita na pasta `data/`

### Instalação

1. **Clone ou extraia** os arquivos em uma pasta do seu servidor web (ex: `/var/www/html/agenda` ou `htdocs/agenda`)

2. **Permissões** — garanta que o PHP pode escrever no arquivo JSON:
   ```bash
   chmod 664 data/compromissos.json
   chmod 775 data/
   ```

3. **Apache** — habilite o mod_rewrite:
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

4. **Acesse** `http://localhost/agenda/` no navegador.

---

## 🔌 API RESTful (PHP)

| Método | Rota                     | Ação                        |
|--------|--------------------------|-----------------------------|
| GET    | `/api/agenda.php`        | Lista todos os compromissos |
| GET    | `/api/agenda.php?id=1`   | Retorna um compromisso      |
| POST   | `/api/agenda.php`        | Cria novo compromisso       |
| PUT    | `/api/agenda.php?id=1`   | Edita compromisso existente |
| DELETE | `/api/agenda.php?id=1`   | Remove compromisso          |

### Exemplo de payload (POST/PUT)
```json
{
  "titulo":    "Reunião de planejamento",
  "descricao": "Alinhamento semanal da equipe.",
  "data":      "2026-04-15",
  "horario":   "09:00",
  "categoria": "trabalho"
}
```

### Categorias disponíveis
- `trabalho`
- `saude`
- `pessoal`
- `outro`

---

## 🛠️ Funcionalidades

- ✅ **CRUD completo** — criar, visualizar, editar e excluir compromissos
- 🔍 **Busca em tempo real** por título e descrição
- 🏷️ **Filtro por categoria** — trabalho, saúde, pessoal, outro
- ⏰ **Contagem regressiva** — dias restantes para cada compromisso
- 📱 **Responsivo** — funciona em desktop, tablet e mobile
- 🌙 **Tema escuro** refinado com paleta dourada
- 💾 **Persistência** — dados salvos em JSON (localStorage no frontend demo)

---

## 📄 Licença

Projeto livre para uso educacional e comercial.
