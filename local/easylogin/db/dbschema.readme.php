<?php
die();

/*
CREATE TABLE `pinion_easylogin_cohorts` (
  `cohort_id` bigint(20) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `grado` tinyint(4) NOT NULL DEFAULT '0',
  `clave_escuela` varchar(6) DEFAULT NULL,
  `nombre` varchar(255) DEFAULT NULL COMMENT 'EL nombre está en la tabla de cohortes, dejo esta columna como referencia al momento de creación de la cohorte. No la usaré'
) ENGINE=InnoDB;


ALTER TABLE `pinion_easylogin_cohorts`
  ADD PRIMARY KEY (`cohort_id`),
  ADD UNIQUE KEY `slug` (`slug`);


CREATE TABLE `pinion_easylogin_estudiantes` (
  `mid` bigint(20) NOT NULL COMMENT 'Moodle user id',
  `cohort_id` bigint(20) NOT NULL,
  `password` varchar(5)  NOT NULL,
  `color` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB;


ALTER TABLE `pinion_easylogin_estudiantes`
  ADD PRIMARY KEY (`mid`,`cohort_id`) USING BTREE;
*/
