# Database Migrations

## Payment table rename

Versions prior to this change created a table called `pagamento` with slightly different column names. The application code now expects the table to be called `pagamentos` with columns `pid`, `data_pagamento` and `estado`.

For existing installations run the script `updater/sql_scripts/003_migrate_pagamento_to_pagamentos.sql` using your database administration tool or the updater utility. This script renames the table and adjusts the column names preserving existing data.

New installations will automatically create the correct table using `updater/sql_scripts/002_create_pagamento.sql`.
