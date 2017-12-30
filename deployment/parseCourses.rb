class Course 
    attr_reader :teacher_name, :name, :number, :section, :type

    def initialize(line)
        values = Course.validateLine(line)
        raise "ERROR " + line if values.nil?
        @number = values[4]
        @name = values[5]
        @teacher_name = values[8]
        @section = values[7]
        @type = values[9]
        
        @number = @number[0..2] + "-" + @number[3..5] + "-" + @numbers[6..7]
    end

    def self.validateLine(line)
        return line.split(',') if !line.to_s.empty?
    end
end

#get the path from the command line argument.
PATH = ARGV[0]
raise "Commandline argument for path must be supplied." if PATH.nil?

courses = Array.new
File.open(PATH).each_line do |line|
    begin
        #Create an item with the line. If line is invalid item.new will throw.
        course = Course.new(line)
        courses.push(course)
    rescue => exception
        puts exception.message
    end
end
