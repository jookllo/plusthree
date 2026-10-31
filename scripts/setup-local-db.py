"""Provision isolated local databases; never print credentials."""
import secrets
import subprocess
from pathlib import Path
password = secrets.token_hex(24)
sql = f"""CREATE DATABASE IF NOT EXISTS plusthree CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS plusthree_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'plusthree'@'%' IDENTIFIED BY '{password}';
ALTER USER 'plusthree'@'%' IDENTIFIED BY '{password}';
GRANT ALL PRIVILEGES ON plusthree.* TO 'plusthree'@'%';
GRANT ALL PRIVILEGES ON plusthree_testing.* TO 'plusthree'@'%';
"""
subprocess.run(['docker', 'exec', '-i', 'eden-test-mysql', 'sh', '-c', 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot'], input=sql, text=True, check=True, stdout=subprocess.DEVNULL)
p = Path(__file__).resolve().parents[1] / 'app' / '.env'
s = p.read_text()
s = '\n'.join('DB_PASSWORD='+password if line.startswith('DB_PASSWORD=') else line for line in s.splitlines())+'\n'
p.write_text(s)
p.chmod(0o600)
t = p.with_name('.env.testing')
t.write_text(s.replace('APP_ENV=local', 'APP_ENV=testing').replace('DB_DATABASE=plusthree\n', 'DB_DATABASE=plusthree_testing\n'))
t.chmod(0o600)
print('Dedicated development and testing databases configured. Credentials saved to ignored environment files.')
