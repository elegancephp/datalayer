### Update

Facilitador pra a query **update**

    Query::update($table);

Para obter o resultado da query, utilize o metodo **run**

    Query::update($table)->run();

Outros metodos para manipular esta query são

**where**
Adiciona um WHERE ao select

    Query::update($table)->where();

**whereNull**
Adiciona um WHERE para ser utilizado na query verificando se um campo é nulo

    Query::update($table)->whereNull($campo, $status = true);

**values**
Define os campos que devem ser alterados com base em um array

    Query::update($table)->values($array);
