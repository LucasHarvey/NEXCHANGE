#ruby deployment/parse_courses.rb deployment/courses.csv deployment/database/latest_courses.csv
require 'csv'
require 'date'

class CourseGroup
    attr_reader :teacher_name, :name, :number, :section_start, :section_end
    attr_writer :section_end
    def initialize(tname, name, number, sec_start, sec_end)
        @teacher_name = tname
        @name = name
        @number = number
        @section_start = sec_start
        @section_end = sec_end
    end
    
    def to_s
        @teacher_name + ";" + @name + ";" + @number + ";" + @section_start + ";" + @section_end
    end
end

class CourseTimes
    attr_reader :days_of_week, :time_start, :time_end, :course_id
    
    def initialize(days_of_week, time_start, time_end, course_id)
        @days_of_week = days_of_week
        @time_start = time_start
        @time_end = time_end
        @course_id = course_id
    end
end

class Course 
    attr_reader :teacher_name, :name, :number, :section, :time_slot, :type

    def initialize(tname, name, number, section, tslot, type)
        @teacher_name = tname
        @name = name
        @number = number
        @section = section
        @time_slot = tslot
        @type = type
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
        
        if(slot.include? ";")
            slot = slot.split(";");
        else
            arr = Array.new
            arr[0] = slot
            slot = arr
        end
        
        slot.each do |time|
            time.strip!
        end
        
        if(teacher_name.nil?)
            teacher_name = "Teacher TBA"
        end
        
        if(teacher_name.include? ", ")
            teacher_name = teacher_name.split(", ")[1] + " " + teacher_name.split(", ")[0]
        end
        number = number[0..2] + "-" + number[3..5] + "-" + number[6..7]
        
        return Course.new(teacher_name, name, number, section, slot, type)
    end
end

#get the path from the command line argument.
INPUT = ARGV[0]
COURSE_FILE_PATH = ARGV[1]
TIME_FILE_PATH = ARGV[2]
SEMESTER_CMD = ARGV[3]
raise "Commandline argument for input path, course output path, time output path, and semester code required" if PATH.nil? || COURSE_FILE_PATH.nil? || TIME_FILE_PATH.nil?

unparsed_courses = Array.new
CSV.foreach(INPUT, encoding: "CP1252") do |line|
    course = Course.factory(line)
    if(course)
        unparsed_courses.push(course)
    end
end

courses_ranges = Array.new
courses_ranges = unparsed_courses.group_by do |course|
    [course.number, course.teacher_name, course.section]
end

sec_courses = Array.new
courses_ranges.each do |key, value|
    if(!(value.kind_of?(Array)))
        course_group = CourseGroup.new(value.teacher_name, value.name, value.number, value.section, value.section)
        sec_courses.push(course_group)
    else
        ordered_courses = value.sort_by do |course|
            begin
                Integer(course.section)
            rescue
                course.section
            end
        end
        course_group = CourseGroup.new(ordered_courses[0].teacher_name, ordered_courses[0].name, ordered_courses[0].number, ordered_courses[0].section, ordered_courses.last.section)
        sec_courses.push(course_group)
    end
end

courses = Array.new
current_courseGroup = sec_courses.shift

for i in 0..sec_courses.length do
    course = sec_courses[i]
    
    if(current_courseGroup.teacher_name == course.teacher_name && current_courseGroup.number == course.number)
        if(current_courseGroup.section_end.to_i == course.section_end.to_i - 1)
            current_courseGroup.section_end = course.section_end
        else
            courses.push(current_courseGroup)
            current_courseGroup = course
            i += 1
        end
    else
        courses.push(current_courseGroup)
        current_courseGroup = course
        i += 1
    end
    
    if(i + 1 >= sec_courses.length)
        courses.push(current_courseGroup)
        break
    end
end

year = Date.today.year
month = Date.today.month - 1
season = "F"
if (month >= 0 && month < 5)
    season = "W"
end
if (month >= 11)
    season = "I"
end
if (month >= 11)
    year = year + 1
end
if (month >= 5 && month < 8)
    season = "S"
end
semester = season + year.to_s


if(! SEMESTER_CMD.nil?)
    semester_codes = ["I", "W", "S", "F"]
    isSemesterCodeOk = semester_codes.include?(SEMESTER_CMD[0])
    yearInput = SEMESTER_CMD[1..5].to_i
    if(SEMESTER_CMD.length == 5 && isSemesterCodeOk && yearInput < 9999 && yearInput > 2000)
        semester = SEMESTER_CMD[0] + yearInput.to_s
    end
end

if(File.file?(COURSE_FILE_PATH))
    File.delete(COURSE_FILE_PATH)
end

file = File.new(COURSE_FILE_PATH,  "w+")

courses.each do |course| 
    file.write(";")
    file.write(course.to_s)
    file.write(";")
    file.write(semester)
    file.write("\n")
end

file.close();

puts courses.length.to_s + " courses parsed."