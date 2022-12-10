### Driver

Cria classes para acesso ao banco de dados

> **IMPORTANTE**
> A criação de drivers de uma conexão, pode ser feita em um banco de dados existente.
> No entanto, é **EXTREMAMENTE RECOMENDADO** que se crie drivers de conexões modeladas com o migiration. 
> De outra forma, ajustes nos drivers podem ser nescessarios, tornando o processo mais custoso do que proveitoso. 

Para se criar um driver, utilize o codigo abaixo no terminal

    php mx dbdriver nomeDaConexão

Isso vai gerar um diretório **\class\Model\NomeDaConexao** com todos os aquivos nescessarios para utilizar o banco. Independente do tipo de banco de dados utilizado.

O diretório, contem 4 partes básicas

 - driver: Contem os arquivos de driver que não devem ser alterados
 - record: Contem as classes para controle de registros
 - table: Comtem as classes para controle de tabelas
 - Db[nomeDaConexão]: Classe de acesso a todos as outras classes

 > Assumiremos a conexão com o nome **main**

 ## A class principal
Praticamente toda utiliazão do driver será feita via clase estatica **Model\\DbMain**
Ela contem uma variavel e um metodo estatico para cada tabela do banco.

Os metodos são utilizados para buscar registros de forma rápida.
Os Objetos são utilizados manipular registros de forma mais especifica

### Metodos de tabela
Os metodos estaticos são atalhos para o metodo **getAuto** da tabela. Mais sobre ele pode ser visto abaixo.

### Objetos de tabela
Os objetos, podem ser acessados via varaivel estatica dentro da classe principal **Model\\DbMain**. Existe um objeto criado para cada tabela.

    DbMain::$produto; //Objeto da tabela produto
    DbMain::$pedido; //Objeto da tabela pedido
    DbMain::$usuario; //Objeto da tabela usuario

Os objetos possuem metodos de busca que facilitam a utilização da tabela
O metodo principal é o **getAuto**, que tenta buscar no banco de dados por registros combinando de varias formas os parametros fornecidos

**getAuto**
Busca um registro baseando-se os parametros fornecidos

    DbMain::$tabela->getAuto();

Ex:

    DbMain::$tabela->getAuto(1); // Retorna o registro com ID = 1
    DbMain::$tabela->getAuto(0); // Retorna um novo registro em branco
    DbMain::$tabela->getAuto('nome=joão'); // Retorna todos os registro com nome = joão
    DbMain::$tabela->getAuto(); // Retorna todos os registros

Outros metodos de busca são:

**getNew**
Retorna um registro NOVO (ainda não existente no banco de dados)

    DbMain::$tabela->getNew();

**getNull**
Retorna um registro NULO (não poderá ser salvo no banco de dados)

    DbMain::$tabela->getNull();

**getOne**
Busca um registro

    DbMain::$tabela->getOne();

**getKey**
Busca um registro baseado na IDKey

    DbMain::$tabela->getKey();

**get**    
Busca varios registros

    DbMain::$tabela->get();

Outros metodos tambem são uteis para o desenvolvimento

**count**
Conta os resultados de uma pesquisa

    DbMain::$tabela->count()

**convert**
Converte um array de resultados em um array de objetos

    DbMain::$tabela->convert($result)

**idKey**
Retorna o idKey de um ID da tabela

    DbMain::$tabela->idKey($id)

**whereIdKey**
Retroan a string de um Where com a idKey

    DbMain::$tabela->whereIdKey($check = null)

### Objeto de registro
Um objeto de registro é recebido sempre que uma busca via driver é executada.
Este objeto, representa um unico registro no banco de dados e pode manipula-lo livremente.

    $produto = DbMain::produto(1); // Retorna um objeto de registro com ID=1
    $produto = DbMain::produto(); // Retorna um objeto de registro novo

A mecanica de utilização, é sempre via metodos. Existe um metodo para cada coluna da tabela. 
Ao chamar um metodo com algum parametro, você esta **alterando** o valor do campo.
Ao chamar o metodo sem nenhum parametro, você está **recuperando** o valor do campo. 


    $produto->nome('feijão'); // Altera o nome do produto para feijão
    $produto->nome(); // Retorna o nome do produto

Alterar um objeto de registro não altera o registro no banco de dados. Para tornar as alterações permanente, deve-se chamar o metodo **_save**

    $produto
        ->nome('feijão') // Altera o nome do produto para feijão
        ->_save(); // Escreveu as alterações no banco de dadoos

Com isso, você pode criar um registro no banco de dados com uma unica linha.

    DbMain::produto()->nome('feijão')->_save();

Outros metodos uteis para manipular registro são

**_setArray**
Define automaticamente varios valores do registro com base em um array
  
    $produto->_setArray($array)

**_drop**
Marca ou desmarca o registro para remoção do banco
  
    $produto->_drop($drop = true)

**id**
Retorna o identificador numerico do registro no banco
  
    $produto->id()

**idKey**
Retorna o identificador codificado do registro no banco
  
    $produto->idKey()

**_array**
Retorna um array com os valores do campo em forma de array
  
    $produto->_array()

**_arrayValues**
Retorna um array com os valores do campo em forma de array tratando os campos de controle e _meta
  
    $produto->_arrayValues()

**_arrayInsert**
Retorna um array com os valores prontos para serem inseridos no banco de dados
  
    $produto->_arrayInsert()

**_checkSave**
Verifica se o registro pode ser salvo no banco de dados
  
    $produto->_checkSave()

**_checkInDB**
Verifica se o registro existe no banco de dados
  
    $produto->_checkInDB()


### Campos de registro

Alguns campos especiais podem ser criados na migration e tem propriedades especiais. 
Um exemplo, é o campo do tipo CODE, que automaticamente codifica tudo que for inserido nele (ideal para senhas)

Caso precise acessar o objeto do campo, e não o valor do campo, basta chamar o campo como um objeto. 

Assim, pode usar funcionalidades do campo para agilizar o desenvolvimento.

    code_check($produto->senha(),'1234'); // Verificando uma senha normalmente

    $produto->senha->check('1234'); // Verificando uma senha via objeto do campo

Isso se torna muito util quando existem campos do tipo **IDX** (chave extrangeira)

    $produto->idx_categoria(1); // Altera a referencia do ID da categoria para 1
    $produto->idx_categoria(); // Recupera a referencia do ID da categoria
    $produto->idx_categoria; // Recupera o objeto de registro da categoria
    $produto->idx_categoria->nome(); // Recupera o nome da categoria referenciada

### Metodos de ação

Os metodos de ação são chamados sempre que uma ação for realizada. 
Isso os torna uma boa opção para automação.

**_onCreate**: Chamado sempre que um registro for criado

**_onUpdate**: Chamado sempre que um registro for salvo

**_onDrop**: Chamado sempre que um registro for salvo com drop(true)

