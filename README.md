# **КОНТРОЛЛЕРЫ И ТЕСТЫ - МИНИМАЛЬНАЯ РЕАЛИЗАЦИЯ**

## **Структура проекта:**
```
CollegeSystem.API/
├── Controllers/
│   ├── StudentsController.cs
│   ├── AssignmentsController.cs
│   ├── ScheduleController.cs
│   ├── PerformanceController.cs
│   └── StatisticsController.cs
├── Models/
│   └── ApiResponse.cs
└── Program.cs

CollegeSystem.API.Tests/
├── Controllers/
│   ├── StudentsControllerTests.cs
│   ├── AssignmentsControllerTests.cs
│   ├── ScheduleControllerTests.cs
│   ├── PerformanceControllerTests.cs
│   └── StatisticsControllerTests.cs
└── CollegeSystem.API.Tests.csproj
```

---

## **1. МОДЕЛИ**

### **ApiResponse.cs**
```csharp
namespace CollegeSystem.API.Models
{
    public class ApiResponse<T>
    {
        public bool Success { get; set; }
        public T Data { get; set; }
        public string ErrorCode { get; set; }
        public string Message { get; set; }
    }
    
    // DTO классы для запросов/ответов
    public class StudentDto
    {
        public string StudentId { get; set; }
        public string FirstName { get; set; }
        public string LastName { get; set; }
        public string GroupId { get; set; }
    }
    
    public class CreateAssignmentRequest
    {
        public string Title { get; set; }
        public string Description { get; set; }
        public string SubjectId { get; set; }
        public string TeacherId { get; set; }
        public string[] GroupIds { get; set; }
        public DateTime Deadline { get; set; }
    }
    
    public class AssignmentResponse
    {
        public string AssignmentId { get; set; }
        public string Title { get; set; }
        public DateTime Deadline { get; set; }
    }
    
    public class UpdateGradeRequest
    {
        public string StudentId { get; set; }
        public int Grade { get; set; }
    }
    
    public class ScheduleResponse
    {
        public string GroupId { get; set; }
        public Dictionary<string, List<LessonDto>> Schedule { get; set; }
    }
    
    public class LessonDto
    {
        public string Subject { get; set; }
        public string Teacher { get; set; }
        public string Room { get; set; }
    }
    
    public class PerformanceResponse
    {
        public string StudentId { get; set; }
        public List<SubjectPerformanceDto> Performance { get; set; }
    }
    
    public class SubjectPerformanceDto
    {
        public string SubjectName { get; set; }
        public int AverageGrade { get; set; }
    }
    
    public class StatisticsResponse
    {
        public string GroupId { get; set; }
        public StatisticsData Data { get; set; }
    }
    
    public class StatisticsData
    {
        public double AverageGrade { get; set; }
        public int TotalStudents { get; set; }
    }
}
```

---

## **2. КОНТРОЛЛЕРЫ**

### **StudentsController.cs**
```csharp
using Microsoft.AspNetCore.Mvc;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace CollegeSystem.API.Controllers
{
    [ApiController]
    [Route("api/v1/[controller]")]
    public class StudentsController : ControllerBase
    {
        [HttpGet("groups/{groupId}/students")]
        public async Task<IActionResult> GetGroupStudents(string groupId)
        {
            try
            {
                // Проверка авторизации
                if (!IsAuthorized())
                    return Unauthorized(new ApiResponse<object> 
                    { 
                        Success = false, 
                        ErrorCode = "UNAUTHORIZED" 
                    });
                
                // Валидация
                if (string.IsNullOrEmpty(groupId))
                    return BadRequest(new ApiResponse<object> 
                    { 
                        Success = false, 
                        ErrorCode = "INVALID_GROUP_ID" 
                    });
                
                // Заглушка данных
                var students = new List<StudentDto>
                {
                    new StudentDto { StudentId = "ST001", FirstName = "Иван", LastName = "Иванов", GroupId = groupId },
                    new StudentDto { StudentId = "ST002", FirstName = "Мария", LastName = "Петрова", GroupId = groupId }
                };
                
                return Ok(new ApiResponse<List<StudentDto>> 
                { 
                    Success = true, 
                    Data = students 
                });
            }
            catch
            {
                return StatusCode(500, new ApiResponse<object> 
                { 
                    Success = false, 
                    ErrorCode = "INTERNAL_ERROR" 
                });
            }
        }
        
        private bool IsAuthorized()
        {
            // Заглушка проверки авторизации
            return Request.Headers.ContainsKey("Authorization");
        }
    }
}
```

### **AssignmentsController.cs**
```csharp
using Microsoft.AspNetCore.Mvc;
using System;
using System.Threading.Tasks;

namespace CollegeSystem.API.Controllers
{
    [ApiController]
    [Route("api/v1/[controller]")]
    public class AssignmentsController : ControllerBase
    {
        [HttpPost]
        public async Task<IActionResult> CreateAssignment([FromBody] CreateAssignmentRequest request)
        {
            try
            {
                // Проверка авторизации
                if (!IsAuthorized())
                    return Unauthorized(new ApiResponse<object> 
                    { 
                        Success = false, 
                        ErrorCode = "UNAUTHORIZED" 
                    });
                
                // Валидация
                if (string.IsNullOrEmpty(request?.Title))
                    return BadRequest(new ApiResponse<object> 
                    { 
                        Success = false, 
                        ErrorCode = "INVALID_TITLE" 
                    });
                
                if (request.Deadline <= DateTime.Now)
                    return BadRequest(new ApiResponse<object> 
                    { 
                        Success = false, 
                        ErrorCode = "INVALID_DEADLINE" 
                    });
                
                // Заглушка создания задания
                var assignment = new AssignmentResponse
                {
                    AssignmentId = $"ASG{DateTime.Now:yyyyMMddHHmmss}",
                    Title = request.Title,
                    Deadline = request.Deadline
                };
                
                return CreatedAtAction(nameof(CreateAssignment), new { id = assignment.AssignmentId },
                    new ApiResponse<AssignmentResponse> 
                    { 
                        Success = true, 
                        Data = assignment 
                    });
            }
            catch
            {
                return StatusCode(500, new ApiResponse<object> 
                { 
                    Success = false, 
                    ErrorCode = "INTERNAL_ERROR" 
                });
            }
        }
        
        [HttpPut("{assignmentId}/grades")]
        public async Task<IActionResult> UpdateGrade(string assignmentId, [FromBody] UpdateGradeRequest request)
        {
            try
            {
                // Проверка авторизации
                if (!IsAuthorized())
                    return Unauthorized(new ApiResponse<object> 
                    { 
                        Success = false, 
                        ErrorCode = "UNAUTHORIZED" 
                    });
                
                // Валидация
                if (string.IsNullOrEmpty(assignmentId))
                    return BadRequest(new ApiResponse<object> 
                    { 
                        Success = false, 
                        ErrorCode = "INVALID_ASSIGNMENT_ID" 
                    });
                
                if (request.Grade < 0 || request.Grade > 100)
                    return BadRequest(new ApiResponse<object> 
                    { 
                        Success = false, 
                        ErrorCode = "INVALID_GRADE" 
                    });
                
                return Ok(new ApiResponse<object> 
                { 
                    Success = true, 
                    Data = new { AssignmentId = assignmentId, Grade = request.Grade }
                });
            }
            catch
            {
                return StatusCode(500, new ApiResponse<object> 
                { 
                    Success = false, 
                    ErrorCode = "INTERNAL_ERROR" 
                });
            }
        }
        
        private bool IsAuthorized()
        {
            return Request.Headers.ContainsKey("Authorization");
        }
    }
}
```

### **ScheduleController.cs**
```csharp
using Microsoft.AspNetCore.Mvc;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace CollegeSystem.API.Controllers
{
    [ApiController]
    [Route("api/v1/[controller]")]
    public class ScheduleController : ControllerBase
    {
        [HttpGet("groups/{groupId}/schedule")]
        public async Task<IActionResult> GetGroupSchedule(string groupId, [FromQuery] string week = null)
        {
            try
            {
                // Проверка авторизации
                if (!IsAuthorized())
                    return Unauthorized(new ApiResponse<object> 
                    { 
                        Success = false, 
                        ErrorCode = "UNAUTHORIZED" 
                    });
                
                // Валидация
                if (string.IsNullOrEmpty(groupId))
                    return BadRequest(new ApiResponse<object> 
                    { 
                        Success = false, 
                        ErrorCode = "INVALID_GROUP_ID" 
                    });
                
                // Заглушка расписания
                var schedule = new ScheduleResponse
                {
                    GroupId = groupId,
                    Schedule = new Dictionary<string, List<LessonDto>>
                    {
                        ["monday"] = new List<LessonDto>
                        {
                            new LessonDto { Subject = "Математика", Teacher = "Иванов И.И.", Room = "101" }
                        }
                    }
                };
                
                return Ok(new ApiResponse<ScheduleResponse> 
                { 
                    Success = true, 
                    Data = schedule 
                });
            }
            catch
            {
                return StatusCode(500, new ApiResponse<object> 
                { 
                    Success = false, 
                    ErrorCode = "INTERNAL_ERROR" 
                });
            }
        }
        
        private bool IsAuthorized()
        {
            return Request.Headers.ContainsKey("Authorization");
        }
    }
}
```

### **PerformanceController.cs**
```csharp
using Microsoft.AspNetCore.Mvc;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace CollegeSystem.API.Controllers
{
    [ApiController]
    [Route("api/v1/[controller]")]
    public class PerformanceController : ControllerBase
    {
        [HttpGet("students/{studentId}/performance")]
        public async Task<IActionResult> GetStudentPerformance(string studentId, 
            [FromQuery] int? semester = null, 
            [FromQuery] string academicYear = null)
        {
            try
            {
                // Проверка авторизации
                if (!IsAuthorized())
                    return Unauthorized(new ApiResponse<object> 
                    { 
                        Success = false, 
                        ErrorCode = "UNAUTHORIZED" 
                    });
                
                // Валидация
                if (string.IsNullOrEmpty(studentId))
                    return BadRequest(new ApiResponse<object> 
                    { 
                        Success = false, 
                        ErrorCode = "INVALID_STUDENT_ID" 
                    });
                
                if (semester.HasValue && (semester < 1 || semester > 2))
                    return BadRequest(new ApiResponse<object> 
                    { 
                        Success = false, 
                        ErrorCode = "INVALID_SEMESTER" 
                    });
                
                // Заглушка успеваемости
                var performance = new PerformanceResponse
                {
                    StudentId = studentId,
                    Performance = new List<SubjectPerformanceDto>
                    {
                        new SubjectPerformanceDto { SubjectName = "Математика", AverageGrade = 85 },
                        new SubjectPerformanceDto { SubjectName = "Физика", AverageGrade = 90 }
                    }
                };
                
                return Ok(new ApiResponse<PerformanceResponse> 
                { 
                    Success = true, 
                    Data = performance 
                });
            }
            catch
            {
                return StatusCode(500, new ApiResponse<object> 
                { 
                    Success = false, 
                    ErrorCode = "INTERNAL_ERROR" 
                });
            }
        }
        
        private bool IsAuthorized()
        {
            return Request.Headers.ContainsKey("Authorization");
        }
    }
}
```

### **StatisticsController.cs**
```csharp
using Microsoft.AspNetCore.Mvc;
using System.Threading.Tasks;

namespace CollegeSystem.API.Controllers
{
    [ApiController]
    [Route("api/v1/[controller]")]
    public class StatisticsController : ControllerBase
    {
        [HttpGet("groups/{groupId}/statistics")]
        public async Task<IActionResult> GetGroupStatistics(string groupId,
            [FromQuery] string period = null,
            [FromQuery] string startDate = null,
            [FromQuery] string endDate = null)
        {
            try
            {
                // Проверка авторизации
                if (!IsAuthorized())
                    return Unauthorized(new ApiResponse<object> 
                    { 
                        Success = false, 
                        ErrorCode = "UNAUTHORIZED" 
                    });
                
                // Валидация
                if (string.IsNullOrEmpty(groupId))
                    return BadRequest(new ApiResponse<object> 
                    { 
                        Success = false, 
                        ErrorCode = "INVALID_GROUP_ID" 
                    });
                
                // Заглушка статистики
                var statistics = new StatisticsResponse
                {
                    GroupId = groupId,
                    Data = new StatisticsData
                    {
                        AverageGrade = 4.2,
                        TotalStudents = 25
                    }
                };
                
                return Ok(new ApiResponse<StatisticsResponse> 
                { 
                    Success = true, 
                    Data = statistics 
                });
            }
            catch
            {
                return StatusCode(500, new ApiResponse<object> 
                { 
                    Success = false, 
                    ErrorCode = "INTERNAL_ERROR" 
                });
            }
        }
        
        private bool IsAuthorized()
        {
            return Request.Headers.ContainsKey("Authorization");
        }
    }
}
```

---

## **3. ЮНИТ-ТЕСТЫ (xUnit)**

### **StudentsControllerTests.cs**
```csharp
using Xunit;
using Microsoft.AspNetCore.Mvc;
using CollegeSystem.API.Controllers;
using CollegeSystem.API.Models;
using System.Collections.Generic;
using Microsoft.AspNetCore.Http;
using Microsoft.Extensions.Logging;
using Moq;

namespace CollegeSystem.API.Tests.Controllers
{
    public class StudentsControllerTests
    {
        private readonly StudentsController _controller;
        
        public StudentsControllerTests()
        {
            _controller = new StudentsController();
        }
        
        [Fact]
        public void GetGroupStudents_ValidRequest_ReturnsOk()
        {
            // Arrange
            var groupId = "IT-21-1";
            _controller.ControllerContext = new ControllerContext
            {
                HttpContext = new DefaultHttpContext()
            };
            _controller.ControllerContext.HttpContext.Request.Headers["Authorization"] = "Bearer token";
            
            // Act
            var result = _controller.GetGroupStudents(groupId).Result;
            
            // Assert
            var okResult = Assert.IsType<OkObjectResult>(result);
            var response = Assert.IsType<ApiResponse<List<StudentDto>>>(okResult.Value);
            Assert.True(response.Success);
            Assert.Equal(2, response.Data.Count);
        }
        
        [Fact]
        public void GetGroupStudents_NoAuth_ReturnsUnauthorized()
        {
            // Arrange
            var groupId = "IT-21-1";
            _controller.ControllerContext = new ControllerContext
            {
                HttpContext = new DefaultHttpContext()
            };
            // Нет заголовка Authorization
            
            // Act
            var result = _controller.GetGroupStudents(groupId).Result;
            
            // Assert
            var unauthorizedResult = Assert.IsType<UnauthorizedObjectResult>(result);
            var response = Assert.IsType<ApiResponse<object>>(unauthorizedResult.Value);
            Assert.False(response.Success);
            Assert.Equal("UNAUTHORIZED", response.ErrorCode);
        }
        
        [Fact]
        public void GetGroupStudents_EmptyGroupId_ReturnsBadRequest()
        {
            // Arrange
            var emptyGroupId = "";
            _controller.ControllerContext = new ControllerContext
            {
                HttpContext = new DefaultHttpContext()
            };
            _controller.ControllerContext.HttpContext.Request.Headers["Authorization"] = "Bearer token";
            
            // Act
            var result = _controller.GetGroupStudents(emptyGroupId).Result;
            
            // Assert
            var badRequestResult = Assert.IsType<BadRequestObjectResult>(result);
            var response = Assert.IsType<ApiResponse<object>>(badRequestResult.Value);
            Assert.Equal("INVALID_GROUP_ID", response.ErrorCode);
        }
    }
}
```

### **AssignmentsControllerTests.cs**
```csharp
using Xunit;
using Microsoft.AspNetCore.Mvc;
using CollegeSystem.API.Controllers;
using CollegeSystem.API.Models;
using System;
using Microsoft.AspNetCore.Http;

namespace CollegeSystem.API.Tests.Controllers
{
    public class AssignmentsControllerTests
    {
        private readonly AssignmentsController _controller;
        
        public AssignmentsControllerTests()
        {
            _controller = new AssignmentsController();
        }
        
        [Fact]
        public void CreateAssignment_ValidRequest_ReturnsCreated()
        {
            // Arrange
            var request = new CreateAssignmentRequest
            {
                Title = "Test Assignment",
                Description = "Test Description",
                SubjectId = "MATH101",
                TeacherId = "TCH001",
                GroupIds = new[] { "IT-21-1" },
                Deadline = DateTime.Now.AddDays(7)
            };
            
            _controller.ControllerContext = new ControllerContext
            {
                HttpContext = new DefaultHttpContext()
            };
            _controller.ControllerContext.HttpContext.Request.Headers["Authorization"] = "Bearer token";
            
            // Act
            var result = _controller.CreateAssignment(request).Result;
            
            // Assert
            var createdResult = Assert.IsType<CreatedAtActionResult>(result);
            var response = Assert.IsType<ApiResponse<AssignmentResponse>>(createdResult.Value);
            Assert.True(response.Success);
            Assert.Contains("ASG", response.Data.AssignmentId);
        }
        
        [Fact]
        public void CreateAssignment_PastDeadline_ReturnsBadRequest()
        {
            // Arrange
            var request = new CreateAssignmentRequest
            {
                Title = "Test",
                Deadline = DateTime.Now.AddDays(-1) // Прошедшая дата
            };
            
            _controller.ControllerContext = new ControllerContext
            {
                HttpContext = new DefaultHttpContext()
            };
            _controller.ControllerContext.HttpContext.Request.Headers["Authorization"] = "Bearer token";
            
            // Act
            var result = _controller.CreateAssignment(request).Result;
            
            // Assert
            var badRequestResult = Assert.IsType<BadRequestObjectResult>(result);
            var response = Assert.IsType<ApiResponse<object>>(badRequestResult.Value);
            Assert.Equal("INVALID_DEADLINE", response.ErrorCode);
        }
        
        [Fact]
        public void UpdateGrade_ValidRequest_ReturnsOk()
        {
            // Arrange
            var assignmentId = "ASG20241215001";
            var request = new UpdateGradeRequest
            {
                StudentId = "ST001",
                Grade = 85
            };
            
            _controller.ControllerContext = new ControllerContext
            {
                HttpContext = new DefaultHttpContext()
            };
            _controller.ControllerContext.HttpContext.Request.Headers["Authorization"] = "Bearer token";
            
            // Act
            var result = _controller.UpdateGrade(assignmentId, request).Result;
            
            // Assert
            var okResult = Assert.IsType<OkObjectResult>(result);
            var response = Assert.IsType<ApiResponse<object>>(okResult.Value);
            Assert.True(response.Success);
        }
        
        [Theory]
        [InlineData(-10)]
        [InlineData(150)]
        public void UpdateGrade_InvalidGrade_ReturnsBadRequest(int invalidGrade)
        {
            // Arrange
            var assignmentId = "ASG001";
            var request = new UpdateGradeRequest
            {
                StudentId = "ST001",
                Grade = invalidGrade
            };
            
            _controller.ControllerContext = new ControllerContext
            {
                HttpContext = new DefaultHttpContext()
            };
            _controller.ControllerContext.HttpContext.Request.Headers["Authorization"] = "Bearer token";
            
            // Act
            var result = _controller.UpdateGrade(assignmentId, request).Result;
            
            // Assert
            var badRequestResult = Assert.IsType<BadRequestObjectResult>(result);
            var response = Assert.IsType<ApiResponse<object>>(badRequestResult.Value);
            Assert.Equal("INVALID_GRADE", response.ErrorCode);
        }
    }
}
```

### **ScheduleControllerTests.cs**
```csharp
using Xunit;
using Microsoft.AspNetCore.Mvc;
using CollegeSystem.API.Controllers;
using CollegeSystem.API.Models;
using Microsoft.AspNetCore.Http;

namespace CollegeSystem.API.Tests.Controllers
{
    public class ScheduleControllerTests
    {
        private readonly ScheduleController _controller;
        
        public ScheduleControllerTests()
        {
            _controller = new ScheduleController();
        }
        
        [Fact]
        public void GetGroupSchedule_ValidRequest_ReturnsOk()
        {
            // Arrange
            var groupId = "IT-21-1";
            _controller.ControllerContext = new ControllerContext
            {
                HttpContext = new DefaultHttpContext()
            };
            _controller.ControllerContext.HttpContext.Request.Headers["Authorization"] = "Bearer token";
            
            // Act
            var result = _controller.GetGroupSchedule(groupId).Result;
            
            // Assert
            var okResult = Assert.IsType<OkObjectResult>(result);
            var response = Assert.IsType<ApiResponse<ScheduleResponse>>(okResult.Value);
            Assert.True(response.Success);
            Assert.Equal(groupId, response.Data.GroupId);
            Assert.NotNull(response.Data.Schedule);
        }
        
        [Fact]
        public void GetGroupSchedule_WithWeekParam_ReturnsOk()
        {
            // Arrange
            var groupId = "IT-21-1";
            var week = "2024-12-09";
            _controller.ControllerContext = new ControllerContext
            {
                HttpContext = new DefaultHttpContext()
            };
            _controller.ControllerContext.HttpContext.Request.Headers["Authorization"] = "Bearer token";
            
            // Act
            var result = _controller.GetGroupSchedule(groupId, week).Result;
            
            // Assert
            Assert.IsType<OkObjectResult>(result);
        }
        
        [Fact]
        public void GetGroupSchedule_NoAuth_ReturnsUnauthorized()
        {
            // Arrange
            var groupId = "IT-21-1";
            _controller.ControllerContext = new ControllerContext
            {
                HttpContext = new DefaultHttpContext()
            };
            // Нет заголовка Authorization
            
            // Act
            var result = _controller.GetGroupSchedule(groupId).Result;
            
            // Assert
            Assert.IsType<UnauthorizedObjectResult>(result);
        }
    }
}
```

### **PerformanceControllerTests.cs**
```csharp
using Xunit;
using Microsoft.AspNetCore.Mvc;
using CollegeSystem.API.Controllers;
using CollegeSystem.API.Models;
using Microsoft.AspNetCore.Http;

namespace CollegeSystem.API.Tests.Controllers
{
    public class PerformanceControllerTests
    {
        private readonly PerformanceController _controller;
        
        public PerformanceControllerTests()
        {
            _controller = new PerformanceController();
        }
        
        [Fact]
        public void GetStudentPerformance_ValidRequest_ReturnsOk()
        {
            // Arrange
            var studentId = "ST001";
            _controller.ControllerContext = new ControllerContext
            {
                HttpContext = new DefaultHttpContext()
            };
            _controller.ControllerContext.HttpContext.Request.Headers["Authorization"] = "Bearer token";
            
            // Act
            var result = _controller.GetStudentPerformance(studentId).Result;
            
            // Assert
            var okResult = Assert.IsType<OkObjectResult>(result);
            var response = Assert.IsType<ApiResponse<PerformanceResponse>>(okResult.Value);
            Assert.True(response.Success);
            Assert.Equal(studentId, response.Data.StudentId);
            Assert.Equal(2, response.Data.Performance.Count);
        }
        
        [Theory]
        [InlineData(1)]
        [InlineData(2)]
        public void GetStudentPerformance_WithValidSemester_ReturnsOk(int semester)
        {
            // Arrange
            var studentId = "ST001";
            _controller.ControllerContext = new ControllerContext
            {
                HttpContext = new DefaultHttpContext()
            };
            _controller.ControllerContext.HttpContext.Request.Headers["Authorization"] = "Bearer token";
            
            // Act
            var result = _controller.GetStudentPerformance(studentId, semester).Result;
            
            // Assert
            Assert.IsType<OkObjectResult>(result);
        }
        
        [Theory]
        [InlineData(0)]
        [InlineData(3)]
        public void GetStudentPerformance_WithInvalidSemester_ReturnsBadRequest(int invalidSemester)
        {
            // Arrange
            var studentId = "ST001";
            _controller.ControllerContext = new ControllerContext
            {
                HttpContext = new DefaultHttpContext()
            };
            _controller.ControllerContext.HttpContext.Request.Headers["Authorization"] = "Bearer token";
            
            // Act
            var result = _controller.GetStudentPerformance(studentId, invalidSemester).Result;
            
            // Assert
            var badRequestResult = Assert.IsType<BadRequestObjectResult>(result);
            var response = Assert.IsType<ApiResponse<object>>(badRequestResult.Value);
            Assert.Equal("INVALID_SEMESTER", response.ErrorCode);
        }
        
        [Fact]
        public void GetStudentPerformance_WithAcademicYear_ReturnsOk()
        {
            // Arrange
            var studentId = "ST001";
            var academicYear = "2024-2025";
            _controller.ControllerContext = new ControllerContext
            {
                HttpContext = new DefaultHttpContext()
            };
            _controller.ControllerContext.HttpContext.Request.Headers["Authorization"] = "Bearer token";
            
            // Act
            var result = _controller.GetStudentPerformance(studentId, null, academicYear).Result;
            
            // Assert
            Assert.IsType<OkObjectResult>(result);
        }
    }
}
```

### **StatisticsControllerTests.cs**
```csharp
using Xunit;
using Microsoft.AspNetCore.Mvc;
using CollegeSystem.API.Controllers;
using CollegeSystem.API.Models;
using Microsoft.AspNetCore.Http;

namespace CollegeSystem.API.Tests.Controllers
{
    public class StatisticsControllerTests
    {
        private readonly StatisticsController _controller;
        
        public StatisticsControllerTests()
        {
            _controller = new StatisticsController();
        }
        
        [Fact]
        public void GetGroupStatistics_ValidRequest_ReturnsOk()
        {
            // Arrange
            var groupId = "IT-21-1";
            _controller.ControllerContext = new ControllerContext
            {
                HttpContext = new DefaultHttpContext()
            };
            _controller.ControllerContext.HttpContext.Request.Headers["Authorization"] = "Bearer token";
            
            // Act
            var result = _controller.GetGroupStatistics(groupId).Result;
            
            // Assert
            var okResult = Assert.IsType<OkObjectResult>(result);
            var response = Assert.IsType<ApiResponse<StatisticsResponse>>(okResult.Value);
            Assert.True(response.Success);
            Assert.Equal(groupId, response.Data.GroupId);
            Assert.Equal(4.2, response.Data.Data.AverageGrade);
        }
        
        [Theory]
        [InlineData("week")]
        [InlineData("month")]
        [InlineData("semester")]
        public void GetGroupStatistics_WithValidPeriod_ReturnsOk(string period)
        {
            // Arrange
            var groupId = "IT-21-1";
            _controller.ControllerContext = new ControllerContext
            {
                HttpContext = new DefaultHttpContext()
            };
            _controller.ControllerContext.HttpContext.Request.Headers["Authorization"] = "Bearer token";
            
            // Act
            var result = _controller.GetGroupStatistics(groupId, period).Result;
            
            // Assert
            Assert.IsType<OkObjectResult>(result);
        }
        
        [Fact]
        public void GetGroupStatistics_WithDateRange_ReturnsOk()
        {
            // Arrange
            var groupId = "IT-21-1";
            var startDate = "2024-12-01";
            var endDate = "2024-12-31";
            _controller.ControllerContext = new ControllerContext
            {
                HttpContext = new DefaultHttpContext()
            };
            _controller.ControllerContext.HttpContext.Request.Headers["Authorization"] = "Bearer token";
            
            // Act
            var result = _controller.GetGroupStatistics(groupId, null, startDate, endDate).Result;
            
            // Assert
            Assert.IsType<OkObjectResult>(result);
        }
        
        [Fact]
        public void GetGroupStatistics_NoAuth_ReturnsUnauthorized()
        {
            // Arrange
            var groupId = "IT-21-1";
            _controller.ControllerContext = new ControllerContext
            {
                HttpContext = new DefaultHttpContext()
            };
            // Нет заголовка Authorization
            
            // Act
            var result = _controller.GetGroupStatistics(groupId).Result;
            
            // Assert
            Assert.IsType<UnauthorizedObjectResult>(result);
        }
    }
}
```

---

## **4. КАК ЗАПУСТИТЬ В VISUAL STUDIO:**

### **Шаг 1: Создать новый проект**
1. File → New → Project
2. Выбрать "ASP.NET Core Web API"
3. Название: `CollegeSystem.API`
4. .NET 6.0 или 8.0
5. Не ставить галочки

### **Шаг 2: Создать папки и файлы**
1. В Solution Explorer правой кнопкой на проект → Add → New Folder
   - `Controllers`
   - `Models`
2. Добавить файлы:
   - В папку Models: `ApiResponse.cs`
   - В папку Controllers: все 5 контроллеров

### **Шаг 3: Создать тестовый проект**
1. Правой кнопкой на Solution → Add → New Project
2. Выбрать "xUnit Test Project"
3. Название: `CollegeSystem.API.Tests`
4. Добавить ссылку на основной проект:
   - Правой кнопкой на тестовый проект → Add → Project Reference
   - Выбрать `CollegeSystem.API`

### **Шаг 4: Установить NuGet пакеты для тестов**
В тестовом проекте через Package Manager Console:
```
Install-Package Moq
Install-Package Microsoft.AspNetCore.Mvc
Install-Package Microsoft.AspNetCore.Http
```

Или через UI: Tools → NuGet Package Manager → Manage NuGet Packages for Solution

### **Шаг 5: Настроить Program.cs**
```csharp
var builder = WebApplication.CreateBuilder(args);

builder.Services.AddControllers();
builder.Services.AddEndpointsApiExplorer();
builder.Services.AddSwaggerGen();

var app = builder.Build();

if (app.Environment.IsDevelopment())
{
    app.UseSwagger();
    app.UseSwaggerUI();
}

app.UseHttpsRedirection();
app.UseAuthorization();
app.MapControllers();

app.Run();
```

### **Шаг 6: Запустить тесты**
1. Build → Build Solution
2. Test → Test Explorer
3. Нажать "Run All Tests"

### **Шаг 7: Запустить API**
Нажать F5 или Debug → Start Debugging

---

## **5. ПРОВЕРКА РАБОТЫ API:**

### **URL для тестирования:**
```
GET    http://localhost:5000/api/v1/students/groups/IT-21-1/students
POST   http://localhost:5000/api/v1/assignments
PUT    http://localhost:5000/api/v1/assignments/ASG001/grades
GET    http://localhost:5000/api/v1/schedule/groups/IT-21-1/schedule
GET    http://localhost:5000/api/v1/performance/students/ST001/performance
GET    http://localhost:5000/api/v1/statistics/groups/IT-21-1/statistics
```

### **Заголовок для авторизации:**
```
Authorization: Bearer test-token
```

### **Пример POST запроса для создания задания:**
```json
{
  "title": "Лабораторная работа №3",
  "description": "Реализация алгоритмов",
  "subjectId": "MATH101",
  "teacherId": "TCH001",
  "groupIds": ["IT-21-1", "IT-21-2"],
  "deadline": "2024-12-20T23:59:59"
}
```

---

**Готово!** У тебя теперь есть 5 рабочих API контроллеров и юнит-тесты для них. Это минимальная рабочая версия для защиты проекта.
