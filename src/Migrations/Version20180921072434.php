<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180921072434 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE INDEX nameDistrict ON district (name)');
        $this->addSql('CREATE INDEX titleJobOffer ON job_offer (title)');
        $this->addSql('CREATE INDEX publicationDateJobOffer ON job_offer (publication_date)');
        $this->addSql('CREATE INDEX usernameUser ON user (username)');
        $this->addSql('CREATE INDEX emailUser ON user (email)');
        $this->addSql('CREATE INDEX firstNameUser ON user (first_name)');
        $this->addSql('CREATE INDEX lastNameUser ON user (last_name)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX nameDistrict ON district');
        $this->addSql('DROP INDEX titleJobOffer ON job_offer');
        $this->addSql('DROP INDEX publicationDateJobOffer ON job_offer');
        $this->addSql('DROP INDEX usernameUser ON user');
        $this->addSql('DROP INDEX emailUser ON user');
        $this->addSql('DROP INDEX firstNameUser ON user');
        $this->addSql('DROP INDEX lastNameUser ON user');
    }
}
