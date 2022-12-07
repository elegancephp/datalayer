# elegance/datalayer

Camada de conexão com banco de dados

    composer require elegance/datalayer

> A biblioteca datalayer não funciona sozinha e deve ser combinada com alguma classe de conexão.

 **[MYSQL](https://github.com/elegancephp/datalayer-mysql)**, **[SQLITE](https://github.com/elegancephp/datalayer-sqlite)**

## Objeto de conexão

Pode-se ter acesso ao objeto de conexão via da classe **\Elegance\Datalayer** no metodo estatico **get**
Passe como parametro, o nome da conexão que deseja recuperar

    Datalayer::get('main'); // Recupera o datalayer de nome main
    Datalayer::get('cache'); // Recupera o datalayer de nome cache
    Datalayer::get('blog'); // Recupera o datalayer de nome blog
    Datalayer::get('loja'); // Recupera o datalayer de nome loja

---

## Veja a [Documentação](https://github.com/elegancephp/datalayer/tree/main/.doc)
 
 - [helper](https://github.com/elegancephp/datalayer/tree/main/.doc/helper)
    - [config](https://github.com/elegancephp/datalayer/tree/main/.doc/helper/config.md)
 - [Query](https://github.com/elegancephp/datalayer/tree/main/.doc/query.md)
    - [Select](https://github.com/elegancephp/datalayer/tree/main/.doc/querySelect.md)
    - [Insert](https://github.com/elegancephp/datalayer/tree/main/.doc/queryInsert.md)
    - [Update](https://github.com/elegancephp/datalayer/tree/main/.doc/queryUpdate.md)
    - [Delete](https://github.com/elegancephp/datalayer/tree/main/.doc/queryDelete.md)

 - [Migration](https://github.com/elegancephp/datalayer/tree/main/.doc/migration.md)

 - [Driver](https://github.com/elegancephp/datalayer/tree/main/.doc/driver.md)

---
### Conexões

 - **[MYSQL](https://github.com/elegancephp/datalayer-mysql)**
 - **[SQLITE](https://github.com/elegancephp/datalayer-sqlite)**