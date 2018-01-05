#sudo ruby deployment/parse_courses.rb deployment/courses.csv /var/lib/mysql-files/upload_courses.csv
require 'csv'
require 'date'

class Course 
    attr_reader :teacher_name, :name, :number, :section, :time_slot

    def initialize(tname, name, number, section, tslot)
        @teacher_name = tname
        @name = name
        @number = number
        @section = section
        @time_slot = tslot
    end
    
    def to_s
        @teacher_name + ";" + @name + ";" + @number + ";" + @section + ";" + @time_slot
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
        slot = values[12]
        
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
        
        return Course.new(teacher_name, name, number, section, slot)
    end
end

#get the path from the command line argument.
PATH = ARGV[0]
UPLOADPATH = ARGV[1]
SEMESTER_CMD = ARGV[2]
raise "Commandline argument for path and upload path must be supplied. Optional semester" if PATH.nil? || UPLOADPATH.nil?

unparsed_courses = Array.new
CSV.foreach(PATH, encoding: "CP1252") do |line|
    course = Course.factory(line)
    if(course)
        unparsed_courses.push(course)
    end
end

courses_ranges = Array.new
courses_ranges = unparsed_courses.group_by do |course|
    [course.number, course.teacher_name, course.time_slot]
end

    end
end

semester = SEMESTER_CMD
if(SEMESTER_CMD.nil?)
    year = Date.today.year
    month = Date.today.month - 1
    today = Date.today.day
    season = "F"
    if (month >= 0 && month < 5)
        season = "W"
    end
    if (month >= 11 || (month == 0 && today<15))
        season = "I"
    end
    if (month >= 11)
        year = year + 1
    end
    if (month >= 5 && month < 8)
        season = "S"
    end
    semester = season + year.to_s
end

if(File.file?(UPLOADPATH))
    File.delete(UPLOADPATH)
end

file = File.new(UPLOADPATH,  "w+")

courses.each do |course| 
    file.write(";")
    file.write(course.teacher_name)
    file.write(";")
    file.write(course.name)
    file.write(";")
    file.write(course.number)
    file.write(";")
    file.write(course.section)
    file.write(";")
    file.write(semester)
    file.write("\n")
end

file.close();

puts courses.length.to_s + " courses parsed."