### Migration

Uma forma simples de criar e manter um banco de dados.

Para criar uma migration, utilize o comando abaixo no **terminal**

    php mx create.migration nomeDaConexao

Isso vai criar um arquivo de nome unico em **migration\nomeDaConexao**

os outros comandos do terminal são

**up**
Executa a proxima migation

    php mx migration.up nomeDaConexao

**down**
Reverte a ultima migration

    php mx migration.down nomeDaConexao

**run**
Executa todas as migrations pendentes

    php mx migration.run nomeDaConexao

**clean**
Reverte todas as migrations executadas

    php mx migration.clean nomeDaConexao


### O arquivo
a estrutura do arquivo conta com uma classe com dois metodos

**up**
Vai ser executado quando a migration for aplicada

**down**
Vai ser executado quando a migration for revertida

Deve-se colocar nestes metodos os codigos referentes a aplicação e a reversão da migration

    new class($datalayer, $mode) extends Elegance\Datalayer\Base\Migration
    {
        function up()
        {
            $this->table('produto')->fields([
                $this->fieldString('nome'),
                $this->fieldString('descricao')
            ]);
        }

        function down()
        {
            $this->table('produto')->drop();
        }
    };

> O **datalayer** controla a execução das migrations esperando que os codigos dentros dos metodos **up** e **down** sejam bem implementados. 

### Manipulando tabelas
Crie ou modifique uma tabela com o comando abaixo

    $this->table('nome_da_tabela');

Para adicionar ou modificar campos, utilize o metodo **fields**;

    $this->table('nome_da_tabela')
        ->fields([
            $this->fieldString('nome_do_campo'),
            ...demais campos da tabela
        ]);

Para remover uma tabela utiilize o metodo **drop**

    $this->table('nome_da_tabela')->drop();

Para remover um campo especifico da tabela, utilize o metodo **field** seguido do metodo **drop**

    $this->table('nome_da_tabela')->field('nome_do_campo')->drop();


### Tipos de campo

**fieldInt**
Retorna um objeto campo do tipo Int
    
    $this->fieldInt($nomeDoCampo,$comentario)

**fieldString**
Retorna um objeto campo do tipo String

    $this->fieldString($nomeDoCampo,$comentario)
    
**fieldText**

Retorna um objeto campo do tipo Text

    $this->fieldText($nomeDoCampo,$comentario)

**fieldFloat**

Retorna um objeto campo do tipo Float

    $this->fieldFloat($nomeDoCampo,$comentario)

**fieldIDX**

Retorna um objeto campo do tipo Idx

    $this->fieldIDX($nomeDoCampo,$comentario)

**fieldIDS**

Retorna um objeto campo do tipo IDs

    $this->fieldIDS($nomeDoCampo,$comentario)

**fieldBoolean**
Retorna um objeto campo do tipo Boolean

    $this->fieldBoolean($nomeDoCampo,$comentario)
    
**fieldMD5**

Retorna um objeto campo do tipo MD5

    $this->fieldMD5($nomeDoCampo,$comentario)

**fieldCode**

Retorna um objeto campo do tipo CODE

    $this->fieldCode($nomeDoCampo,$comentario)

**fieldEmail**

Retorna um objeto campo do tipo Email

    $this->fieldEmail($nomeDoCampo,$comentario)

**fieldLog**

Retorna um objeto campo do tipo Log

    $this->fieldLog($nomeDoCampo,$comentario)

**fieldList**

Retorna um objeto campo do tipo List

    $this->fieldList($nomeDoCampo,$comentario)

**fieldTag**

Retorna um objeto campo do tipo Tag

    $this->fieldTag($nomeDoCampo,$comentario)

**fieldConfig**
Retorna um objeto campo do tipo Config

    $this->fieldConfig($nomeDoCampo,$comentario)

**fieldStatus**
Retorna um objeto campo do tipo Status

    $this->fieldStatus($nomeDoCampo,$comentario)

**fieldTime**
Retorna um objeto campo do tipo Time

    $this->fieldTime($nomeDoCampo,$comentario)
    

### Querys em migration

Pode-se utilizar querys em migration utilizando o metodo **query**:

    $this->query(...);

As querys serão excutadas **depois** das alterações no banco

> **IMPORTANTE**
> Não execute o metodo **run()** em uma query dentro da migration. Isso pode causar resultados inesperados. A migration se encarrega de executa estar querys na orem apropriada. 
