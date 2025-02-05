# Platoforma de gestão de utilizadores,clientes,produtos e com criaçao de reservas.

Link site online: http://pap14403.byethost6.com/
email : teste@teste.com
palavra passe: teste

Como colocar o projeto num servidor (Byethost)
1- Criar uma Conta no Byethost
Acesse https://byet.host e clique em Free Hosting.
Clique em Sign Up e preencha seus dados.
Após criar a conta, acesse o cPanel do Byethost.

2 - Enviar os Arquivos do Seu Site via FTP
Baixe um cliente FTP como FileZilla (https://filezilla-project.org/).
Conecte ao servidor com os seguintes dados:
Host: ftp.yourwebsite.byethost.com
Usuário: fornecido pelo cPanel
Senha: fornecida pelo cPanel
No FileZilla, vá até a pasta htdocs e envie os seus arquivos.


3 - Configurar Base de Dados MySQL
No cPanel, vá até MySQL Databases.
Crie uma base de dados.
No arquivo db.config.php, configure a conexão.

4 - Testar o Site
Acesse seu domínio: http://yourwebsite.byethost.com
Se houver erros, verifique o cPanel > Error Logs.
