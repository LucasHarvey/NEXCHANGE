use nexchange;

LOAD DATA INFILE '/home/ubuntu/workspace/deployment/test.csv' 
    IGNORE 
    INTO TABLE courses 
    (@Regroupement,@NumeroDiscipline,@TitreCourtDiscipline,@TitreDiscipline,@NumeroCours,@TitreCours,@DescriptionCours,@NumeroGroupe,@NomPrenomEnseignant,@TypeComposante,@TitreTypeComposante,@MontantApresTaxeFraisSupplementaire,@Horaire,@MessageHoraireEtudiant,@NumeroProgramme,@TitreProgramme,@SortString,@NoSession,@Rang) 
    FIELDS TERMINATED BY ',' 
    IGNORE 1 LINES 
    SET 
        teacher_fullname = @NomPrenomEnseignant,
        course_name = @TitreCours,
        course_number = @NumeroCours,
        section = @NumeroGroupe,
        semester = "F2017";