### Query
---
**execute**
Executa uma query em uma conexão registrada

    Datalayer::get('main')->executeQuery($querySting,$queryData)

---

**execute list**
Executa multiplas querys em uma conexão registrada

    Datalayer::get('main')->executeQueryList($querySting,$queryData)

---

> Embora as querys em string possam ser uma saída em algumas situações, não recomendados o seu uso direto. 

**considere**

 - [Select](https://github.com/elegancephp/datalayer/tree/main/.doc/querySelect.md)
 - [Insert](https://github.com/elegancephp/datalayer/tree/main/.doc/queryInsert.md)
 - [Update](https://github.com/elegancephp/datalayer/tree/main/.doc/queryUpdate.md)
 - [Delete](https://github.com/elegancephp/datalayer/tree/main/.doc/queryDelete.md)