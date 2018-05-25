#ruby deployment/parse_courses.rb deployment/courses.csv deployment/database/latest_courses.csv
require 'csv'
require 'date'
require 'securerandom'
require 'pp'

class CourseGroup
    attr_reader :course_id, :teacher_name, :name, :number, :sections, :time_slots
    
    def initialize(tname, name, number, section, time_slots)
        @course_id = SecureRandom.uuid
        @teacher_name = tname
        @name = name
        @number = number
        @sections = [section]
        @time_slots = time_slots
    end
    
    def addSection(section)
        @sections.push(section)
    end
    
    def addSections(sections)
        @sections.concat(sections)
    end
    
    def sectionsToString
        sectionString = ""
        firstNumberSwitch = true
        
        sections = @sections.sort
            
        firstSection = sections.shift

        prevSection = firstSection
        while(sections.length != 0)
            nextSection = sections.shift
            
            if(nextSection != prevSection + 1) #Were the previous sections continuous?
                if(firstNumberSwitch == false)
                    sectionString = sectionString + ","
                end
                
                if(firstSection != prevSection)
                    sectionString = sectionString + firstSection.to_s+"-"+prevSection.to_s
                else
                    sectionString = sectionString + prevSection.to_s
                end
                firstNumberSwitch = false
                firstSection = nextSection
            end
            
            prevSection = nextSection
        end
        
        if(firstNumberSwitch == false)
            sectionString = sectionString + ","
        end
        if(firstSection != prevSection)
            sectionString = sectionString + firstSection.to_s+"-"+prevSection.to_s
        else
            sectionString = sectionString + prevSection.to_s
        end
        
        #TODO: Clean me up - repetitive code!
        
        return sectionString
    end
    
    def to_s
        @course_id + ";" + @teacher_name + ";" + @name + ";" + @number + ";" + self.sectionsToString()
    end
    
    def getTimeSlots
        parsed_times = Array.new
        @time_slots.each do |time|
            parsed_times.push(CourseTime.factory(time))
        end
        return parsed_times
    end
    
    def self.factory(fromCourse)
        return CourseGroup.new(fromCourse.teacher_name, fromCourse.name, fromCourse.number, fromCourse.section, fromCourse.time_slot)
    end
    
end

class CourseTime
    attr_reader :days_of_week, :time_start, :time_end
    
    def initialize(days_of_week, time_start, time_end)
        @days_of_week = days_of_week
        @time_start = time_start
        @time_end = time_end
    end
    
    def self.factory(fromString)
        splitDays = fromString.split(":")
        splitHours = splitDays[1].split("-")
        return CourseTime.new(splitDays[0], splitHours[0], splitHours[1])
    end
end

class Course 
    attr_reader :teacher_name, :name, :number, :section, :time_slot, :type

    def initialize(teacher_name, name, number, section, time_slot, type)
        @teacher_name = teacher_name
        @name = name
        @number = number
        @section = section
        @time_slot = time_slot
        @type = type
    end
    
    def addTimeSlot(timeSlot)
        @time_slot.concat(timeSlot)
    end
    
    def to_s
        #Todo: compound time_slot to readable array.
        return @teacher_name + ";" + @name + ";" + @number + ";" + @section + ";" + @time_slot.to_s
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
        
        if section.match(/^\d+$/)
            section = section.to_i
        else
            raise "SECTION NOT A NUMBER" + section
        end
        
        return Course.new(teacher_name, name, number, section, slot, type)
    end
end

INPUT_PATH = ARGV[0]
COURSE_FILE_PATH = ARGV[1]
TIME_FILE_PATH = ARGV[2]
SEMESTER_CMD = ARGV[3]

def getCoursesFromFile
    #get the path from the command line argument.
    raise "Commandline argument for input path, course output path, time output path, and semester code required" if INPUT_PATH.nil? || COURSE_FILE_PATH.nil? || TIME_FILE_PATH.nil?
    
    unparsed_courses = Array.new
    CSV.foreach(INPUT_PATH, encoding: "CP1252") do |line|
        course = Course.factory(line)
        if(course)
            unparsed_courses.push(course)
        end
    end
    return unparsed_courses
end

#Merges multiple courses that could have the same teacher and the same time.
def handleSectionExceptions(sections)
    similarCourses = {
        #"603-102-MQ" => "603-102-MQ", #Course # that other courses will map to
        #"603-200-AB" => "603-102-MQ",
        #"603-103-MQ" => "603-102-MQ"
    }
    
    finalCourses = Hash.new
    
    sections.each do |key, value|
        course_sections = value.group_by do |course|
            [course.section]
        end
        course_section_parsed = Array.new
        course_sections.each do |sectionNumber, singleCourseSections|
            course = singleCourseSections.shift
            while(singleCourseSections.length != 0)
                nextCourse = singleCourseSections.shift
                course.addTimeSlot(nextCourse.time_slot)
            end
            course_section_parsed.push(course)
        end
        value = course_section_parsed
        
        
        theKey = key[0]
        if similarCourses.has_key?(theKey)
            theKey = similarCourses[theKey]
        end
        
        if !finalCourses.has_key?(theKey)
            finalCourses[theKey] = value
        else
            finalCourses[theKey].concat(value)
        end
    end
    
    return finalCourses
end

def groupCoursesByNumber(courses)
    sections = courses.group_by do |course|
        [course.number]
    end
    
    sections = handleSectionExceptions(sections)
    
    return sections
end

def groupCoursesByTime(unparsed_courses)
    courses = Array.new
    unparsed_courses.each do |course_number, a_courses|
        #TODO only one course in the course number would result in error here
        
        if a_courses.length == 1
            a_courses = Array.new(a_courses)
        end
        
        course_times = a_courses.group_by do |course|
            [course.time_slot, course.teacher_name]
        end
        
        
        courses.push(course_times.values)
    end
    
    return courses
end

def createCourseGroup(parsed_times)
    course_groups = Array.new
    parsed_times.each do |courses|
        courses.each do |time_courses|
            
            #convert sections into numbers first.
            time_courses.sort_by do |course|
                course.section
            end
            
            firstCourse = time_courses.shift
            group = CourseGroup.factory(firstCourse)
            
            group.addSections(time_courses.map{|course| course.section })
            
            course_groups.push(group)
        end
    end
    return course_groups
end

#Todo: exception for english courses?! those that are equivalent ex 603-102-MQ and 603-200-AB

unparsed_courses = getCoursesFromFile
parsed_courses = groupCoursesByNumber(unparsed_courses)
parsed_sections = groupCoursesByTime(parsed_courses)
parsed_groups = createCourseGroup(parsed_sections)

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

parsed_groups.each do |course| 
    file.write(course.to_s)
    file.write(";")
    file.write(semester)
    file.write("\n")
end

file.close();

if(File.file?(TIME_FILE_PATH))
    File.delete(TIME_FILE_PATH)
end

file = File.new(TIME_FILE_PATH,  "w+")

parsed_groups.each do |course| 
    course.time_slots.each do |timeSlot|
        file.write(course.course_id)
        file.write(";")
        file.write(timeSlot.to_s)
        file.write("\n")
    end
end

file.close();

puts parsed_groups.length.to_s + " courses parsed."