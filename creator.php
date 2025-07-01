<?php
class Creator
{
    private PDO $con;
    private string $servidor;
    private string $banco;
    private string $usuario;
    private string $senha;
    private array $tabelas = [];

    public function __construct(array $config)
    {
        $this->servidor = $config['servidor'] ?? '';
        $this->banco    = $config['banco'] ?? '';
        $this->usuario  = $config['usuario'] ?? '';
        $this->senha    = $config['senha'] ?? '';
    }

    public function executar()
    {
        $this->criarDiretorios();
        $this->conectar();
        $this->buscarTabelas();
        $this->gerarModels();
        $this->gerarConexao();
        $this->gerarControllers();
    }

    private function criarDiretorios(): void
    {
        $dirs = ["sistema", "sistema/model", "sistema/control", "sistema/view", "sistema/dao"];
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }
    }

    private function conectar(): void
    {
        try {
            $this->con = new PDO(
                "mysql:host={$this->servidor};dbname={$this->banco};port=3306",
                $this->usuario,
                $this->senha
            );
        } catch (PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }

    private function buscarTabelas(): void
    {
        $stmt = $this->con->query("SHOW TABLES");
        $this->tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function buscarColunas(string $tabela): array
    {
        $stmt = $this->con->query("SHOW COLUMNS FROM {$tabela}");
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    private function gerarModels(): void
    {
        foreach ($this->tabelas as $tabela) {
            $classe = ucfirst($tabela);
            $atributos = '';
            $getSet = '';

            foreach ($this->buscarColunas($tabela) as $coluna) {
                $nome = $coluna->Field;
                $atributos .= "\tprivate \${$nome};\n";
                $metodo = ucfirst($nome);

                $getSet .= <<<EOF
    public function get{$metodo}() {
        return \$this->{$nome};
    }

    public function set{$metodo}(\${$nome}) {
        \$this->{$nome} = \${$nome};
    }

EOF;
            }

            $codigo = <<<PHP
<?php
class {$classe} {
{$atributos}

{$getSet}}
PHP;
            file_put_contents("sistema/model/{$classe}.php", $codigo);
        }
    }

    private function gerarConexao(): void
    {
        $codigo = <<<PHP
<?php
class Conexao {
    private string \$host = 'localhost';
    private string \$banco = 'plataforma';
    private string \$usuario = 'root';
    private string \$senha = 'bancodedados';

    public function conectar(): PDO {
        return new PDO(
            "mysql:host={\$this->host};dbname={\$this->banco};port=3306",
            \$this->usuario,
            \$this->senha
        );
    }
}
PHP;
        file_put_contents("sistema/model/conexao.php", $codigo);
    }

    private function gerarControllers(): void
    {
        foreach ($this->tabelas as $tabela) {
            $classe = ucfirst($tabela);
            $codigo = <<<PHP
<?php
require_once '../model/{$classe}.php';
require_once '../dao/{$classe}DAO.php';

class {$classe}Control {
    private \$dao;
    private \${$tabela};

    public function __construct() {
        \$this->{$tabela} = new {$classe}();
        \$this->dao = new {$classe}DAO();
        // verificar ações aqui
    }
}

new {$classe}Control();
PHP;
            file_put_contents("sistema/control/{$classe}Control.php", $codigo);
        }
    }
}
