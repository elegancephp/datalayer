### Insert

Facilitador pra a query **insert**

    Query::insert($table);

Para obter o resultado da query, utilize o metodo **run**

    Query::insert($table)->run($datalayer);

Outro metodo para manipular a query é

**values**
Define os registros para inserção
    
    Query::insert($table)->values([]);

A query **insert** retorna o ID do registro inserido

    $id = Query::insert($table)->values([])->run($datalayer);
