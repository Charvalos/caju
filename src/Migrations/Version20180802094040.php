<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180802094040 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE closing (id INT AUTO_INCREMENT NOT NULL, closing_type_id INT NOT NULL, date DATETIME NOT NULL, INDEX IDX_5542EBB4507CAC82 (closing_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE closing_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE closing ADD CONSTRAINT FK_5542EBB4507CAC82 FOREIGN KEY (closing_type_id) REFERENCES closing_type (id)');
        $this->addSql('ALTER TABLE job_offer ADD closing_id INT NOT NULL');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4E814E9932 FOREIGN KEY (closing_id) REFERENCES closing (id)');
        $this->addSql('CREATE INDEX IDX_288A3A4E814E9932 ON job_offer (closing_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4E814E9932');
        $this->addSql('ALTER TABLE closing DROP FOREIGN KEY FK_5542EBB4507CAC82');
        $this->addSql('DROP TABLE closing');
        $this->addSql('DROP TABLE closing_type');
        $this->addSql('DROP INDEX IDX_288A3A4E814E9932 ON job_offer');
        $this->addSql('ALTER TABLE job_offer DROP closing_id');
    }
}
