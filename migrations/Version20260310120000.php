<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260310120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema for schools, classes, students, and admins';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE admins (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_ADMINS_USERNAME (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE schools (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, mobile VARCHAR(20) NOT NULL, school_code VARCHAR(50) NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_SCHOOLS_MOBILE (mobile), UNIQUE INDEX UNIQ_SCHOOLS_SCHOOL_CODE (school_code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE classes (id INT AUTO_INCREMENT NOT NULL, school_id INT NOT NULL, class VARCHAR(20) NOT NULL, division VARCHAR(10) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX uniq_school_class_division (school_id, class, division), INDEX IDX_CLASSES_SCHOOL_ID (school_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE students (id INT AUTO_INCREMENT NOT NULL, school_id INT NOT NULL, class_id INT NOT NULL, admission_no VARCHAR(30) NOT NULL, name VARCHAR(180) NOT NULL, phone VARCHAR(20) NOT NULL, parent_name VARCHAR(180) DEFAULT NULL, address LONGTEXT DEFAULT NULL, photo VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX uniq_school_admission (school_id, admission_no), INDEX IDX_STUDENTS_SCHOOL_ID (school_id), INDEX IDX_STUDENTS_CLASS_ID (class_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE classes ADD CONSTRAINT FK_CLASSES_SCHOOL_ID FOREIGN KEY (school_id) REFERENCES schools (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE students ADD CONSTRAINT FK_STUDENTS_SCHOOL_ID FOREIGN KEY (school_id) REFERENCES schools (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE students ADD CONSTRAINT FK_STUDENTS_CLASS_ID FOREIGN KEY (class_id) REFERENCES classes (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE students DROP FOREIGN KEY FK_STUDENTS_CLASS_ID');
        $this->addSql('ALTER TABLE students DROP FOREIGN KEY FK_STUDENTS_SCHOOL_ID');
        $this->addSql('ALTER TABLE classes DROP FOREIGN KEY FK_CLASSES_SCHOOL_ID');
        $this->addSql('DROP TABLE students');
        $this->addSql('DROP TABLE classes');
        $this->addSql('DROP TABLE schools');
        $this->addSql('DROP TABLE admins');
    }
}
