<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181002124906 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE document_job_offer');
        $this->addSql('DROP TABLE document_postulation');
        $this->addSql('ALTER TABLE document ADD update_at DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE document_job_offer (document_id INT NOT NULL, job_offer_id INT NOT NULL, INDEX IDX_3AB212EFC33F7837 (document_id), INDEX IDX_3AB212EF3481D195 (job_offer_id), PRIMARY KEY(document_id, job_offer_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document_postulation (document_id INT NOT NULL, postulation_id INT NOT NULL, INDEX IDX_58D479F1C33F7837 (document_id), INDEX IDX_58D479F1D749FDF1 (postulation_id), PRIMARY KEY(document_id, postulation_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE document_job_offer ADD CONSTRAINT FK_3AB212EF3481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE document_job_offer ADD CONSTRAINT FK_3AB212EFC33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE document_postulation ADD CONSTRAINT FK_58D479F1C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE document_postulation ADD CONSTRAINT FK_58D479F1D749FDF1 FOREIGN KEY (postulation_id) REFERENCES postulation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE document DROP update_at');
    }
}
