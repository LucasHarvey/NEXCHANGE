require 'csv'

class Course 
    attr_reader :teacher_name, :name, :number, :section

    def initialize(tname, name, number, section)
        @teacher_name = tname
        @name = name
        @number = number
        @section = section
    end
    
    def self.factory(values)
        if(values.empty?)
            return false
        end
        number = values[4]
        name = values[5]
        teacher_name = values[8]
        section = values[7]
        type = values[9]
        
        if(type[0] != "C" && type != "I")
            return false
        end
        
        if(teacher_name.nil?)
            teacher_name = "Teacher TBA"
        end
        
        if(teacher_name.include? ", ")
            teacher_name = teacher_name.split(", ")[1] + " " + teacher_name.split(", ")[0]
        end
        number = number[0..2] + "-" + number[3..5] + "-" + number[6..7]
        
        return Course.new(teacher_name, name, number, section)
    end
end

#get the path from the command line argument.
PATH = ARGV[0]
UPLOADPATH = ARGV[1]
raise "Commandline argument for path and upload path must be supplied." if PATH.nil? || UPLOADPATH.nil?

courses = Array.new
CSV.foreach(PATH, encoding: "CP1252") do |line|
    course = Course.factory(line)
    if(course) 
        courses.push(course)
    end
end

SEMESTER = "F2017"

if(File.file?(UPLOADPATH))
    File.delete(UPLOADPATH)
end

file = File.new(UPLOADPATH,  "w+")

courses.each do |course| 
   file.write(course.teacher_name)
   file.write(";")
   file.write(course.name)
   file.write(";")
   file.write(course.number)
   file.write(";")
   file.write(course.section)
   file.write(";")
   file.write(SEMESTER)
   file.write("\n")
end

file.close();