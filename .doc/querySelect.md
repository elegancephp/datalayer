### Select

Facilitador pra a query **select**

    Query::select($table);

Para obter o resultado da query, utilize o metodo **run**

    Query::select($table)->run();

Outros metodos para manipular esta query são

**where**
Adiciona um WHERE ao select
    
    Query::select($table)->where();

**whereNull**
Adiciona um WHERE para ser utilizado na query verificando se um campo é nulo
    
    Query::select($table)->whereNull($campo, $status = true);

**fields**
Define os campos que devem ser retornados no select, NULL ou * retorna todos os campos
    
    Query::select($table)->fields($fields);

**order**
Define a ordem dos resultados
    
    Query::select($table)->order($fields, $asc = true);

**limit**
Define a quantidade maxima de valores retornados no select
    
    Query::select($table)->limit($limit);
