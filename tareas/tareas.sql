
#Tasks of mysql
#***********************************************
#***********************************************

#Insert into v_tanques_h

INSERT INTO v_tanques_h
SELECT NOW() now,sub.* 
FROM(
	SELECT id,tanque,producto,volumen_actual,volumen_faltante,altura,agua,temperatura,fecha_hora_veeder,fecha_hora_s,estado,volumen 
	FROM tanques
	WHERE estado = 1
) sub
ORDER BY sub.producto DESC;


# Insert into v_inv

INSERT INTO v_inv
SELECT NOW() now,
inv.id,inv.descripcion, inv.rubro,inv.activo,inv.existencia,
inv.precio,inv.costo,inv.costo_prom,inv.codigo,inv.ncc_vt,inv.ncc_cv,inv.ncc_al,inv.inv_cunidad,inv.inv_cproducto
FROM inv
WHERE inv.rubro = 'Aceites';


# Insert into v_invd

INSERT INTO v_invd 
SELECT invd.*, NOW() now
FROM invd;



# Insert into v_saldos
 
INSERT INTO v_saldos
SELECT NOW() now,sub.* 
FROM(
	SELECT cxc.cliente,cli.alias,cli.nombre,cli.tipodepago,
	ROUND(SUM(IF(cxc.tm = 'H',-cxc.importe,cxc.importe)),2) importe,
	IF(cli.tipodepago = 'Prepago',2,1) orden
	FROM cxc,cli
	WHERE cxc.cliente = cli.id AND cxc.cliente > 0 
	AND cli.tipodepago IN ('Credito','Prepago','Tarjeta')
	AND cli.activo = 'Si'
	GROUP BY cxc.cliente
	ORDER BY cli.tipodepago,cxc.cliente
) sub
ORDER BY sub.orden,sub.tipodepago,sub.cliente;


# Insert into v_carabonos

INSERT INTO v_carabonos
SELECT NOW() now,sub.* 
FROM(
	SELECT 
		C.cliente, 
		cli.nombre, 
		cli.tipodepago, 
		SUM(IFNULL(inicial, 0)) inicial,
		SUM(IFNULL(cargo, 0)) cargos,
		SUM(IFNULL(abono, 0)) abonos,
		ROUND(SUM(IFNULL(inicial, 0)) + SUM(IFNULL(cargo, 0)) - SUM(IFNULL(abono, 0)) , 2) importe,
		CASE WHEN cli.tipodepago IN ('Credito', 'Tarjeta') THEN 1 ELSE 2 END orden
	FROM cli
	JOIN (
		SELECT 
		   cxc.cliente,
		   0 inicial,
		   ROUND(SUM(cxc.importe), 2) cargo,
		   0 abono
		FROM cxc 
		JOIN rm ON cxc.referencia = rm.id AND cxc.producto NOT LIKE '-' 
		JOIN ct ON ct.id = rm.corte AND DATE(ct.fecha) BETWEEN DATE_SUB(CURDATE(),INTERVAL 1 MONTH) AND CURDATE() 
		WHERE cxc.tm = 'C'
		GROUP BY cxc.cliente

		UNION ALL

		SELECT 
		   cxc.cliente,
		   0 inicial,
		   ROUND(SUM(CASE WHEN tm = 'C' THEN importe ELSE 0 END), 2) cargo, 
		   ROUND(SUM(CASE WHEN tm = 'C' THEN 0 ELSE importe END), 2) saldo 
		FROM cxc 
		WHERE producto LIKE '-'
		AND DATE(fecha) BETWEEN DATE_SUB(CURDATE(),INTERVAL 1 MONTH) AND CURDATE()
		GROUP BY cxc.cliente

		UNION ALL

		SELECT
		  cxc.cliente,
		  ROUND(SUM(CASE WHEN tm = 'C' THEN importe ELSE -importe END), 2) inicial,
		  0 cargo,
		  0 abono
		FROM cxc
		WHERE DATE(cxc.fecha) < DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
		GROUP BY cxc.cliente
	) C ON C.cliente = cli.id AND cli.tipodepago IN ('Credito', 'Tarjeta', 'Prepago') AND cli.activo = 'Si'
GROUP by cliente
ORDER BY orden, tipodepago, cliente
) sub;
