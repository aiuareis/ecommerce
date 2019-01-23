select NO_FUNCIONARIO NOME,max(qtd1) qtd1,max(qtd2) qtd2from
(select NO_FUNCIONARIO,case when tipo = 1 then qtd else 0 end as qtd1,case when tipo = 2 then qtd else 0 end as
qtd2from(select f.NO_FUNCIONARIO, count(an.nr_protocolo)
qtd , 1 as tipofrom DBA_DNRC.ANDAMENTO anLEFT
JOIN FUNCIONARIO f ON an.SQ_FUNCIONARIO=f.SQ_FUNCIONARIOwhere an.SI_SECAO_ORIGEM = :secao
and an.SI_SECAO_DESTINO='EX' AND an.CO_DESPACHO='005'AND an.DT_ANDAMENTO between " + dataIand " + dataFgroup
by f.NO_FUNCIONARIO unionselect f.NO_FUNCIONARIO,
count(distinct an.nr_protocolo) qtd , 2 as tipofrom DBA_DNRC.ANDAMENTO anLEFT JOIN FUNCIONARIO f
ON an.SQ_FUNCIONARIO=f.SQ_FUNCIONARIOwhere an.SI_SECAO_ORIGEM = :secao
and an.SI_SECAO_DESTINO = :secaoAutenticacao ANDan.DT_ANDAMENTO
between + dataIand dataFgroup by f.NO_FUNCIONARIO))group by NO_FUNCIONARIO