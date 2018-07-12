<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180712065412 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE canton (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(50) NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category_job_offer (category_id INT NOT NULL, job_offer_id INT NOT NULL, INDEX IDX_EFEAE87E12469DE2 (category_id), INDEX IDX_EFEAE87E3481D195 (job_offer_id), PRIMARY KEY(category_id, job_offer_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE city (id INT AUTO_INCREMENT NOT NULL, district_id INT NOT NULL, name VARCHAR(50) NOT NULL, npa INT NOT NULL, INDEX IDX_2D5B0234B08FA272 (district_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE closing (id INT AUTO_INCREMENT NOT NULL, closing_type_id INT NOT NULL, closing_date DATE NOT NULL, INDEX IDX_5542EBB4507CAC82 (closing_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE closing_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE district (id INT AUTO_INCREMENT NOT NULL, canton_id INT NOT NULL, name VARCHAR(20) NOT NULL, INDEX IDX_31C154878D070D0B (canton_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(50) NOT NULL, format VARCHAR(10) NOT NULL, INDEX IDX_D8698A76A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document_job_offer (document_id INT NOT NULL, job_offer_id INT NOT NULL, INDEX IDX_3AB212EFC33F7837 (document_id), INDEX IDX_3AB212EF3481D195 (job_offer_id), PRIMARY KEY(document_id, job_offer_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document_postulation (document_id INT NOT NULL, postulation_id INT NOT NULL, INDEX IDX_58D479F1C33F7837 (document_id), INDEX IDX_58D479F1D749FDF1 (postulation_id), PRIMARY KEY(document_id, postulation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE job_offer (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, closing_id INT DEFAULT NULL, offer_type_id INT DEFAULT NULL, category_id INT DEFAULT NULL, title VARCHAR(50) NOT NULL, description LONGTEXT NOT NULL, publication_date DATETIME DEFAULT NULL, renewal_date DATETIME DEFAULT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_288A3A4EA76ED395 (user_id), INDEX IDX_288A3A4E814E9932 (closing_id), INDEX IDX_288A3A4E64444A9A (offer_type_id), INDEX IDX_288A3A4E12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE offer_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE postulation (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, job_offer_id INT NOT NULL, postulation_date DATE NOT NULL, status TINYINT(1) NOT NULL, response_date DATE DEFAULT NULL, INDEX IDX_DA7D4E9BA76ED395 (user_id), INDEX IDX_DA7D4E9B3481D195 (job_offer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, city_id INT NOT NULL, username VARCHAR(50) NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(50) NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, phone_n1 INT NOT NULL, phone_n2 INT DEFAULT NULL, address VARCHAR(50) NOT NULL, birthdate DATE NOT NULL, last_login DATETIME DEFAULT NULL, picture VARCHAR(10) DEFAULT NULL, hash VARCHAR(255) DEFAULT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_8D93D6498BAC62AF (city_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE category_job_offer ADD CONSTRAINT FK_EFEAE87E12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_job_offer ADD CONSTRAINT FK_EFEAE87E3481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT FK_2D5B0234B08FA272 FOREIGN KEY (district_id) REFERENCES district (id)');
        $this->addSql('ALTER TABLE closing ADD CONSTRAINT FK_5542EBB4507CAC82 FOREIGN KEY (closing_type_id) REFERENCES closing_type (id)');
        $this->addSql('ALTER TABLE district ADD CONSTRAINT FK_31C154878D070D0B FOREIGN KEY (canton_id) REFERENCES canton (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE document_job_offer ADD CONSTRAINT FK_3AB212EFC33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE document_job_offer ADD CONSTRAINT FK_3AB212EF3481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE document_postulation ADD CONSTRAINT FK_58D479F1C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE document_postulation ADD CONSTRAINT FK_58D479F1D749FDF1 FOREIGN KEY (postulation_id) REFERENCES postulation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4E814E9932 FOREIGN KEY (closing_id) REFERENCES closing (id)');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4E64444A9A FOREIGN KEY (offer_type_id) REFERENCES offer_type (id)');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4E12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE postulation ADD CONSTRAINT FK_DA7D4E9BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE postulation ADD CONSTRAINT FK_DA7D4E9B3481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6498BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE district DROP FOREIGN KEY FK_31C154878D070D0B');
        $this->addSql('ALTER TABLE category_job_offer DROP FOREIGN KEY FK_EFEAE87E12469DE2');
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4E12469DE2');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6498BAC62AF');
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4E814E9932');
        $this->addSql('ALTER TABLE closing DROP FOREIGN KEY FK_5542EBB4507CAC82');
        $this->addSql('ALTER TABLE city DROP FOREIGN KEY FK_2D5B0234B08FA272');
        $this->addSql('ALTER TABLE document_job_offer DROP FOREIGN KEY FK_3AB212EFC33F7837');
        $this->addSql('ALTER TABLE document_postulation DROP FOREIGN KEY FK_58D479F1C33F7837');
        $this->addSql('ALTER TABLE category_job_offer DROP FOREIGN KEY FK_EFEAE87E3481D195');
        $this->addSql('ALTER TABLE document_job_offer DROP FOREIGN KEY FK_3AB212EF3481D195');
        $this->addSql('ALTER TABLE postulation DROP FOREIGN KEY FK_DA7D4E9B3481D195');
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4E64444A9A');
        $this->addSql('ALTER TABLE document_postulation DROP FOREIGN KEY FK_58D479F1D749FDF1');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76A76ED395');
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4EA76ED395');
        $this->addSql('ALTER TABLE postulation DROP FOREIGN KEY FK_DA7D4E9BA76ED395');
        $this->addSql('DROP TABLE canton');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE category_job_offer');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE closing');
        $this->addSql('DROP TABLE closing_type');
        $this->addSql('DROP TABLE district');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE document_job_offer');
        $this->addSql('DROP TABLE document_postulation');
        $this->addSql('DROP TABLE job_offer');
        $this->addSql('DROP TABLE offer_type');
        $this->addSql('DROP TABLE postulation');
        $this->addSql('DROP TABLE user');
    }
}
