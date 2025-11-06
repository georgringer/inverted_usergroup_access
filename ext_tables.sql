CREATE TABLE tt_content (
	fe_group_negate int(2) DEFAULT '0' NOT NULL,
);

CREATE TABLE pages (
	fe_group_negate int(2) DEFAULT '0' NOT NULL,
);

CREATE TABLE sys_file_reference (
	fe_group varchar(11) DEFAULT '' NOT NULL,
	fe_group_negate int(2) DEFAULT '0' NOT NULL,
);
