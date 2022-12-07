### Delete

Facilitador pra a query **delete**

    Query::delete($table);

Para obter o resultado da query, utilize o metodo **run**

    Query::delete($table)->run($datalayer);

Outros metodos para manipular esta query são

**where**
Adiciona um WHERE a query

    Query::delete($table)->where();

**whereNull**
Adiciona um WHERE para ser utilizado na query verificando se um campo é nulo

    Query::delete($table)->whereNull($campo, $status = true)

**order**
Define a ordem dos resultados

    Query::delete($table)->order($fields, $asc = true);

**limit**
Define a quantidade maxima de valores removidos

    Query::delete($table)->limit($limit);