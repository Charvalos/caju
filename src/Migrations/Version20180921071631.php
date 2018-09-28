<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180921071631 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE INDEX nameCategory ON category (title)');
        $this->addSql('CREATE INDEX nameCity ON city (name)');
        $this->addSql('CREATE INDEX npaCity ON city (npa)');
        $this->addSql('CREATE INDEX allCity ON city (npa, name)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX nameCategory ON category');
        $this->addSql('DROP INDEX nameCity ON city');
        $this->addSql('DROP INDEX npaCity ON city');
        $this->addSql('DROP INDEX allCity ON city');
    }
}
