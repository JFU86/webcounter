CREATE TABLE [webcounter_referer] (
[id] INTEGER  NOT NULL PRIMARY KEY,
[referer] VARCHAR(255)  UNIQUE NOT NULL,
[anzahl] INTEGER(10) DEFAULT '0' NOT NULL,
[erstbesuch] Datetime  NULL,
[letztbesuch] Datetime  NULL
);
/* SPLIT */
CREATE TABLE [webcounter_reload] (
[id] INTEGER  NOT NULL PRIMARY KEY,
[ipaddress] VARCHAR(30)  NOT NULL,
[visit] INTEGER(10) DEFAULT '0' NOT NULL
);
/* SPLIT */
CREATE TABLE [webcounter_visitor] (
[datum] DATE  NOT NULL,
[stunde] VARCHAR(2)  NOT NULL,
[anzahl] INTEGER(10)  NOT NULL,
PRIMARY KEY ([datum],[stunde])
);