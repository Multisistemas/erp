-- VENTAS CONTRIBUYENTES
SELECT @row := @row + 1 corr, 
       f.datec fecha_emision, 
       f.ref_client corr_pre_impreso, 
       s.nom nombre_contribuyente, 
       s.siret num_registro, 
       sum(f.total) monto,
       sum(f.`tva`) debito_fiscal,
       sum(fe.vat_invoice_retention) impuesto_recibido,
       sum(f.total_ttc) ventas_totales
  FROM `llx_facture` f,
       `llx_facture_extrafields` fe,
       `llx_societe` s
 WHERE f.rowid = fe.fk_object
   AND f.fk_soc = s.rowid
--   AND f.ref_client LIKE 'CCF%'
   AND f.datec BETWEEN '2019-12-01' AND '2019-12-31'
GROUP BY 1,2,3,4,5
ORDER BY 2